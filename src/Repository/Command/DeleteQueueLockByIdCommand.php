<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Repository\Command;

use Magento\Framework\App\ResourceConnection;

class DeleteQueueLockByIdCommand
{
    public function __construct(private ResourceConnection $resourceConnection)
    {
    }

    public function execute(int $id): void
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName('queue_lock'),
            [ 'id = ?' => $id ]
        );
    }
}
