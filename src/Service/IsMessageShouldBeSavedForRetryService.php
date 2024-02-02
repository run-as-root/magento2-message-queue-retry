<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Service;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use RunAsRoot\MessageQueueRetry\Repository\Query\FindQueueRetryLimitByTopicNameQuery;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

class IsMessageShouldBeSavedForRetryService
{
    public function __construct(
        private readonly MessageQueueRetryConfig $messageQueueRetryConfig,
        private readonly GetMessageRetriesCountService $getMessageRetriesCountService,
        private readonly FindQueueRetryLimitByTopicNameQuery $findQueueRetryLimitByTopicNameQuery
    ) {
    }

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

        $retryLimit = $this->findQueueRetryLimitByTopicNameQuery->execute($topicName);

        if ($retryLimit === null) {
            return false;
        }

        return $totalRetries >= $retryLimit;
    }
}
