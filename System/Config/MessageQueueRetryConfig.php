<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\System\Config;

use JsonException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MessageQueueRetryConfig
{
    public const MAIN_TOPIC_NAME = 'main_topic_name';
    public const DELAY_TOPIC_NAME = 'delay_topic_name';
    public const RETRY_LIMIT = 'retry_limit';
    private const XML_PATH_DELAY_QUEUES = 'message_queue_retry/general/delay_queues';
    private const XML_PATH_ENABLE_DELAY_QUEUE = 'message_queue_retry/general/enable_delay_queue';

    public function __construct(private ScopeConfigInterface $scopeConfig)
    {
    }

    public function isDelayQueueEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_DELAY_QUEUE);
    }

    /**
     * @throws JsonException
     */
    public function getDelayQueues(): array
    {
        $configValues = $this->scopeConfig->getValue(self::XML_PATH_DELAY_QUEUES);

        if (!$configValues) {
            return [];
        }

        $configValues = json_decode($configValues, true, 512, JSON_THROW_ON_ERROR);

        $result = [];

        foreach ($configValues as $configValue) {
            $mainTopicName = $configValue[self::MAIN_TOPIC_NAME] ?? null;
            $result[$mainTopicName] = [
                self::MAIN_TOPIC_NAME => $mainTopicName,
                self::DELAY_TOPIC_NAME => $configValue[self::DELAY_TOPIC_NAME] ?? null,
                self::RETRY_LIMIT => (int)$configValue[self::RETRY_LIMIT] ?? null,
            ];
        }

        return $result;
    }
}
