<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Service;

use Exception;
use JsonException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\Envelope;
use Magento\Framework\MessageQueue\QueueInterface;
use PhpAmqpLib\Wire\AMQPTable;
use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeCreatedException;
use RunAsRoot\MessageQueueRetry\Model\MessageFactory;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

class HandleQueueFailureService
{
    public function __construct(
        private MessageQueueRetryConfig $messageQueueRetryConfig,
        private MessageFactory $messageFactory,
        private MessageRepository $messageRepository
    ) {
    }

    /**
     * @throws JsonException
     * @throws ConnectionLostException
     * @throws MessageCouldNotBeCreatedException
     */
    public function execute(QueueInterface $queue, Envelope $message, Exception $exception): void
    {
        if (!$this->messageQueueRetryConfig->isDelayQueueEnabled()) {
            $queue->reject($message, false, $exception->getMessage());
            return;
        }

        $messageProperties = $message->getProperties();
        $applicationHeaders = $messageProperties['application_headers'] ?? null;

        // If there are no application headers, then it is the first time the message has been processed.
        if (!$applicationHeaders instanceof AMQPTable) {
            $queue->reject($message, false, $exception->getMessage());
            return;
        }

        $totalRetries = $this->getTotalRetries($applicationHeaders);

        $topicName = $messageProperties['topic_name'] ?? null;

        if (!$topicName) {
            $queue->reject($message, false, $exception->getMessage());
            return;
        }

        $queueConfiguration = $this->getQueueConfiguration($topicName);

        if (!$queueConfiguration) {
            $queue->reject($message, false, $exception->getMessage());
            return;
        }

        $retryLimit = $queueConfiguration[MessageQueueRetryConfig::RETRY_LIMIT] ?? 0;

        if ($totalRetries >= $retryLimit) {
            // If message reaches the retry limit, then it is moved to the run_as_root_message table
            $messageModel = $this->messageFactory->create();
            $messageModel->setTopicName($topicName);
            $messageModel->setMessageBody($message->getBody());
            $messageModel->setFailureDescription($exception->getMessage());
            $messageModel->setTotalRetries($totalRetries);
            $this->messageRepository->create($messageModel);

            // and discard the RabbitMQ's message.
            $queue->acknowledge($message);
            return;
        }

        $queue->reject($message, false, $exception->getMessage());
    }

    private function getTotalRetries(AMQPTable $applicationHeaders): int
    {
        $totalRetries = 1;

        if (isset($applicationHeaders->getNativeData()['x-death'][0]['count'])) {
            // +1 is added because the first time the message is processed, it is not counted as a retry.
            $totalRetries = $applicationHeaders->getNativeData()['x-death'][0]['count'] + 1;
        }

        return $totalRetries;
    }

    /**
     * @throws JsonException
     */
    private function getQueueConfiguration(string $topicName): ?array
    {
        $delayQueueConfiguration = $this->messageQueueRetryConfig->getDelayQueues();
        return $delayQueueConfiguration[$topicName] ?? null;
    }
}
