<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Queue;

use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\Phrase;
use RunAsRoot\MessageQueueRetry\Exception\InvalidMessageQueueConnectionTypeException;
use RunAsRoot\MessageQueueRetry\Exception\InvalidPublisherConfigurationException;

class Publisher
{
    public function __construct(
        private ExchangeRepository $exchangeRepository,
        private EnvelopeFactory $envelopeFactory,
        private PublisherConfig $publisherConfig
    ) {
    }

    /**
     * @throws InvalidPublisherConfigurationException
     * @throws InvalidMessageQueueConnectionTypeException
     */
    public function publish(string $topicName, string $data): void
    {
        $envelopeData = $this->getEnvelopeData($topicName, $data);
        $envelope = $this->envelopeFactory->create($envelopeData);

        try {
            $connectionName = $this->publisherConfig->getPublisher($topicName)->getConnection()->getName();
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage() instanceof Phrase ? $e->getMessage() : new Phrase($e->getMessage());
            throw new InvalidPublisherConfigurationException($exceptionMessage, $e, $e->getCode());
        }

        if ($connectionName !== 'amqp') {
            throw new InvalidMessageQueueConnectionTypeException(__('Only AMQP connection is supported.'));
        }

        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);

        $exchange->enqueue($topicName, $envelope);
    }

    private function getEnvelopeData(string $topicName, string $data): array
    {
        return [
            'body' => $data,
            'properties' => [
                'delivery_mode' => 2,
                // md5() here is not for cryptographic use.
                // phpcs:ignore Magento2.Security.InsecureFunction
                'message_id' => md5(gethostname() . microtime(true) . uniqid($topicName, true)),
            ],
        ];
    }
}
