<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Repository;

use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteQueueLockByIdCommand;

class QueueLockRepository
{
    public function __construct(private DeleteQueueLockByIdCommand $deleteQueueLockByIdCommand)
    {
    }

    public function deleteById(int $id): void
    {
        $this->deleteQueueLockByIdCommand->execute($id);
    }
}
