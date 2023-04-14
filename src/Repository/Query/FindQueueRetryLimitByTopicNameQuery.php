<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Repository\Query;

use Magento\Framework\Config\DataInterface;
use RunAsRoot\MessageQueueRetry\Config\QueueRetryConfigInterface;

class FindQueueRetryLimitByTopicNameQuery
{
    public function __construct(private DataInterface $configStorage)
    {
    }

    public function execute(string $topicName): ?int
    {
        $topics = $this->configStorage->get(QueueRetryConfigInterface::CONFIG_KEY_NAME);

        if (!$topics) {
            return null;
        }

        $queueRetryTopic = $topics[$topicName] ??  null;

        if (!$queueRetryTopic) {
            return null;
        }

        $retryLimit = $queueRetryTopic[QueueRetryConfigInterface::RETRY_LIMIT] ?? null;

        return $retryLimit ? (int)$retryLimit : null;
    }
}
