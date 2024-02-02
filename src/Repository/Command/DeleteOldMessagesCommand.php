<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Repository\Command;

use Magento\Framework\App\ResourceConnection;
use RunAsRoot\MessageQueueRetry\Api\Data\QueueErrorMessageInterface;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

class DeleteOldMessagesCommand
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly MessageQueueRetryConfig $messageQueueRetryConfig
    ) {
    }

    public function execute(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(QueueErrorMessageInterface::TABLE_NAME);
        $totalDays = $this->messageQueueRetryConfig->getTotalDaysToKeepMessages();
        $connection->delete($tableName, [ "created_at < DATE_SUB(NOW(), INTERVAL $totalDays DAY)" ]);
    }
}
