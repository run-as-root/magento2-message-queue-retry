<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Model\ResourceModel\Message;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use RunAsRoot\MessageQueueRetry\Model\Message as Model;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\Message as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
