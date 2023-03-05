<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Repository\Command;

use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeDeletedException;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\Message as ResourceModel;
use RunAsRoot\MessageQueueRetry\Repository\Query\FindMessageByIdQuery;

class DeleteMessageByIdCommand
{
    public function __construct(
        private ResourceModel $resourceModel,
        private FindMessageByIdQuery $findMessageById
    ) {
    }

    /**
     * @throws MessageCouldNotBeDeletedException
     */
    public function execute(int $entityId): void
    {
        try {
            $model = $this->findMessageById->execute($entityId);
            $this->resourceModel->delete($model);
        } catch (\Exception $e) {
            throw new MessageCouldNotBeDeletedException(
                __('Message with id %1 could not deleted', $entityId),
                $e
            );
        }
    }
}
