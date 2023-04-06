<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Queue;

use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\Phrase;
use RunAsRoot\MessageQueueRetry\Exception\InvalidPublisherConfigurationException;
use RunAsRoot\MessageQueueRetry\Exception\InvalidQueueConnectionTypeException;

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
     * @throws InvalidQueueConnectionTypeException
     */
    public function publish(string $topicName, string $data): void
    {
        $envelopeData = $this->getEnvelopeData($topicName, $data);
        $envelope = $this->envelopeFactory->create($envelopeData);

        try {
            $connectionName = $this->publisherConfig->getPublisher($topicName)->getConnection()->getName();
        } catch (\Exception $e) {
            $exceptionMessage = new Phrase($e->getMessage());
            throw new InvalidPublisherConfigurationException($exceptionMessage, $e, $e->getCode());
        }

        if ($connectionName !== 'amqp') {
            throw new InvalidQueueConnectionTypeException(__('Only AMQP connection is supported.'));
        }

        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);

        $exchange->enqueue($topicName, $envelope);
    }

    /**
     * @return array<string, mixed>
     */
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
