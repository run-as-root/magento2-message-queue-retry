<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Api\Data;

interface MessageInterface
{
    public const TABLE_NAME = 'run_as_root_message';
    public const ENTITY_ID = 'entity_id';
    public const TOPIC_NAME = 'topic_name';
    public const MESSAGE_BODY = 'message_body';
    public const FAILURE_DESCRIPTION = 'failure_description';
    public const RESOURCE_ID = 'resource_id';
    public const TOTAL_RETRIES = 'total_retries';
    public const CREATED_AT = 'created_at';

    public function getTopicName(): string;
    public function setTopicName(string $value): void;

    public function getMessageBody(): string;
    public function setMessageBody(string $value): void;

    public function getFailureDescription(): string;
    public function setFailureDescription(string $value): void;

    public function getResourceId(): string;
    public function setResourceId(string $value): void;

    public function getTotalRetries(): int;
    public function setTotalRetries(int $value): void;

    public function getCreatedAt(): string;
    public function setCreatedAt(string $value): void;
}
