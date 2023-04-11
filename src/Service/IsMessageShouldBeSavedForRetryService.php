<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Service;

use JsonException;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

class IsMessageShouldBeSavedForRetryService
{
    public function __construct(
        private MessageQueueRetryConfig $messageQueueRetryConfig,
        private GetMessageRetriesCountService $getMessageRetriesCountService
    ) {
    }

    /**
     * @throws JsonException
     */
    public function execute(EnvelopeInterface $message): bool
    {
        if (!$this->messageQueueRetryConfig->isDelayQueueEnabled()) {
            return false;
        }

        $totalRetries = $this->getMessageRetriesCountService->execute($message);

        if ($totalRetries === 0) {
            return false;
        }

        $messageProperties = $message->getProperties();
        $topicName = $messageProperties['topic_name'] ?? null;

        if (!$topicName) {
            return false;
        }

        $queueConfiguration = $this->getQueueConfiguration($topicName);

        if (!$queueConfiguration) {
            return false;
        }

        $retryLimit = $queueConfiguration[MessageQueueRetryConfig::RETRY_LIMIT] ?? 0;

        return $totalRetries >= $retryLimit;
    }

    /**
     * @throws JsonException
     * @return array<string,mixed>|null
     */
    private function getQueueConfiguration(string $topicName): ?array
    {
        $delayQueueConfiguration = $this->messageQueueRetryConfig->getDelayQueues();
        return $delayQueueConfiguration[$topicName] ?? null;
    }
}
