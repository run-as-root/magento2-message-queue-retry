<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Repository\Command;

use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeDeletedException;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\MessageResource as ResourceModel;

class DeleteMessageCommand
{
    public function __construct(private ResourceModel $resourceModel)
    {
    }

    /**
     * @throws MessageCouldNotBeDeletedException
     */
    public function execute(Message $message): void
    {
        try {
            $this->resourceModel->delete($message);
        } catch (\Exception $e) {
            throw new MessageCouldNotBeDeletedException(
                __('Message with id %1 could not deleted', $message->getId()),
                $e
            );
        }
    }
}
