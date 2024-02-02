<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class MessageQueueRetryConfig
{
    private const XML_PATH_ENABLE_DELAY_QUEUE = 'message_queue_retry/general/enable_delay_queue';
    private const XML_PATH_TOTAL_DAYS_TO_KEEP_MESSAGES = 'message_queue_retry/general/total_days_to_keep_messages';
    private const DEFAULT_TOTAL_DAYS_TO_KEEP_MESSAGES = 30;

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function isDelayQueueEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_DELAY_QUEUE);
    }

    public function getTotalDaysToKeepMessages(): int
    {
        /** @var string $configValue */
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_TOTAL_DAYS_TO_KEEP_MESSAGES);

        if (!$configValue) {
            return self::DEFAULT_TOTAL_DAYS_TO_KEEP_MESSAGES;
        }

        return (int)$configValue;
    }
}
