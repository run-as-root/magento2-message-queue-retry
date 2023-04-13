<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class MessageQueueRetryConfig
{
    private const XML_PATH_ENABLE_DELAY_QUEUE = 'message_queue_retry/general/enable_delay_queue';

    public function __construct(private ScopeConfigInterface $scopeConfig)
    {
    }

    public function isDelayQueueEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_DELAY_QUEUE);
    }
}
