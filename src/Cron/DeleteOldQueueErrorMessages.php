<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Cron;

use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteOldMessagesCommand;

class DeleteOldQueueErrorMessages
{
    public function __construct(private readonly DeleteOldMessagesCommand $deleteOldMessagesCommand)
    {
    }

    public function execute(): void
    {
        $this->deleteOldMessagesCommand->execute();
    }
}
