<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Model;

use Magento\Framework\Model\AbstractModel;
use RunAsRoot\MessageQueueRetry\Api\Data\MessageInterface;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\Message\MessageCollection;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\MessageResource;

class Message extends AbstractModel implements MessageInterface
{
    protected function _construct(): void
    {
        $this->_init(MessageResource::class);
        $this->_collectionName = MessageCollection::class;
    }

    public function getTopicName(): string
    {
        return $this->getData(self::TOPIC_NAME);
    }

    public function setTopicName(string $value): void
    {
        $this->setData(self::TOPIC_NAME, $value);
    }

    public function getMessageBody(): string
    {
        return $this->getData(self::MESSAGE_BODY);
    }

    public function setMessageBody(string $value): void
    {
        $this->setData(self::MESSAGE_BODY, $value);
    }

    public function getFailureDescription(): string
    {
        return $this->getData(self::FAILURE_DESCRIPTION);
    }

    public function setFailureDescription(string $value): void
    {
        $this->setData(self::FAILURE_DESCRIPTION, $value);
    }

    public function getResourceId(): string
    {
        return $this->getData(self::RESOURCE_ID);
    }

    public function setResourceId(string $value): void
    {
        $this->setData(self::RESOURCE_ID, $value);
    }

    public function getTotalRetries(): int
    {
        return $this->getData(self::TOTAL_RETRIES);
    }

    public function setTotalRetries(int $value): void
    {
        $this->setData(self::TOTAL_RETRIES, $value);
    }

    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $value): void
    {
        $this->setData(self::CREATED_AT, $value);
    }
}
