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
        $configKey = QueueRetryConfigInterface::CONFIG_KEY_NAME . '/' . $topicName;
        $queueRetryTopic = $this->configStorage->get($configKey);

        if (!$queueRetryTopic) {
            return null;
        }

        $retryLimitKey = QueueRetryConfigInterface::RETRY_LIMIT;
        return isset($queueRetryTopic[$retryLimitKey]) ? (int)$queueRetryTopic[$retryLimitKey] : null;
    }
}
