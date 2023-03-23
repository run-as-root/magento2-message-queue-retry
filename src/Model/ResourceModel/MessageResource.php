<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use RunAsRoot\MessageQueueRetry\Api\Data\MessageInterface;

class MessageResource extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(MessageInterface::TABLE_NAME, MessageInterface::ENTITY_ID);
    }
}
