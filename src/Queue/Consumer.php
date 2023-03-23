<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Queue;

use Exception;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\CallbackInvokerInterface;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageLockException;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;
use RunAsRoot\MessageQueueRetry\Repository\QueueLockRepository;
use RunAsRoot\MessageQueueRetry\Service\HandleQueueFailureService;

/**
 * This class is a rewrite for the original \Magento\Framework\MessageQueue\Consumer class.
 * It is used to add the ability to retry messages that have failed to process.
 */
class Consumer implements ConsumerInterface
{
    public function __construct(
        private CallbackInvokerInterface $invoker,
        private MessageEncoder $messageEncoder,
        private ConsumerConfigurationInterface $configuration,
        private LoggerInterface $logger,
        private ConsumerConfig $consumerConfig,
        private CommunicationConfig $communicationConfig,
        private QueueRepository $queueRepository,
        private MessageController $messageController,
        private MessageValidator $messageValidator,
        private EnvelopeFactory $envelopeFactory,
        private QueueLockRepository $queueLockRepository,
        private HandleQueueFailureService $handleQueueFailureService
    ) {
    }

    public function process($maxNumberOfMessages = null): void
    {
        $queue = $this->configuration->getQueue();
        $maxIdleTime = $this->configuration->getMaxIdleTime();
        $sleep = $this->configuration->getSleep();

        if (!isset($maxNumberOfMessages)) {
            $queue->subscribe($this->getTransactionCallback($queue));
        } else {
            $this->invoker->invoke(
                $queue,
                $maxNumberOfMessages,
                $this->getTransactionCallback($queue),
                $maxIdleTime,
                $sleep
            );
        }
    }

    private function getTransactionCallback(QueueInterface $queue): \Closure
    {
        return function (EnvelopeInterface $message) use ($queue) {
            /** @var LockInterface $lock */
            $lock = null;

            try {
                $topicName = $message->getProperties()['topic_name'];
                $topicConfig = $this->communicationConfig->getTopic($topicName);
                $lock = $this->messageController->lock($message, $this->configuration->getConsumerName());

                if ($topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS]) {
                    $responseBody = $this->dispatchMessage($message, true);
                    $responseMessage = $this->envelopeFactory->create(
                        [ 'body' => $responseBody, 'properties' => $message->getProperties() ]
                    );
                    $this->sendResponse($responseMessage);
                } else {
                    $allowedTopics = $this->configuration->getTopicNames();

                    if (!in_array($topicName, $allowedTopics)) {
                        $queue->reject($message);
                        return;
                    }

                    $this->dispatchMessage($message);
                }

                $queue->acknowledge($message);
            } catch (MessageLockException $exception) {
                $queue->acknowledge($message);
            } catch (ConnectionLostException $exception) {
                if ($lock) {
                    $this->queueLockRepository->deleteById((int)$lock->getId());
                }
            } catch (NotFoundException $exception) {
                $queue->acknowledge($message);
                $this->logger->warning($exception->getMessage());
            } catch (Exception $exception) {
                $this->handleQueueFailureService->execute($queue, $message, $exception);

                if ($lock) {
                    $this->queueLockRepository->deleteById((int)$lock->getId());
                }
            }
        };
    }

    /**
     * Send RPC response message.
     *
     * @throws LocalizedException
     */
    private function sendResponse(EnvelopeInterface $envelope): void
    {
        $messageProperties = $envelope->getProperties();
        $connectionName = $this->consumerConfig->getConsumer($this->configuration->getConsumerName())->getConnection();
        $queue = $this->queueRepository->get($connectionName, $messageProperties['reply_to']);
        $queue->push($envelope);
    }

    /**
     * Decode message and invoke callback method, return reply back for sync processing.
     *
     * @throws LocalizedException
     */
    private function dispatchMessage(EnvelopeInterface $message, $isSync = false): ?string
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $handlers = $this->configuration->getHandlers($topicName);
        $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());

        if (isset($decodedMessage)) {
            $messageSchemaType = $this->configuration->getMessageSchemaType($topicName);

            if ($messageSchemaType === CommunicationConfig::TOPIC_REQUEST_TYPE_METHOD) {
                foreach ($handlers as $callback) {
                    // The `array_values` is a workaround to ensure the same behavior in PHP 7 and 8.
                    $result = call_user_func_array($callback, array_values($decodedMessage));
                    return $this->processSyncResponse($topicName, $result);
                }
            } else {
                foreach ($handlers as $callback) {
                    $result = call_user_func($callback, $decodedMessage);

                    if ($isSync) {
                        return $this->processSyncResponse($topicName, $result);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Validate and encode synchronous handler output.
     *
     * @throws LocalizedException
     */
    private function processSyncResponse($topicName, $result): string
    {
        if (isset($result)) {
            $this->messageValidator->validate($topicName, $result, false);
            return $this->messageEncoder->encode($topicName, $result, false);
        }

        throw new LocalizedException(new Phrase('No reply message resulted in RPC.'));
    }
}
