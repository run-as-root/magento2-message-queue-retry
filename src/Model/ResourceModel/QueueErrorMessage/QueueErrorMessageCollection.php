<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Model\ResourceModel\QueueErrorMessage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessage as Model;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\QueueErrorMessageResource as ResourceModel;

class QueueErrorMessageCollection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
