<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use RunAsRoot\MessageQueueRetry\Api\Data\QueueErrorMessageInterface;

class QueueErrorMessageResource extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(QueueErrorMessageInterface::TABLE_NAME, QueueErrorMessageInterface::ENTITY_ID);
    }
}
