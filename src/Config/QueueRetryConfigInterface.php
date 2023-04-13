<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Config;

interface QueueRetryConfigInterface
{
    public const CONFIG_KEY_NAME = 'queue_retry_topics';
    public const CACHE_KEY = 'queue_retry_config';
    public const FILE_NAME = 'queue_retry.xml';
    public const TOPIC_NAME = 'topic_name';
    public const RETRY_LIMIT = 'retry_limit';
    public const XSD_FILE_URN = 'urn:RunAsRoot:module:RunAsRoot_MessageQueueRetry:/etc/queue_retry.xsd';
}
