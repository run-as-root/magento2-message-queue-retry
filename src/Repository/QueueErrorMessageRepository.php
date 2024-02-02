<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Repository;

use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeCreatedException;
use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeDeletedException;
use RunAsRoot\MessageQueueRetry\Exception\MessageNotFoundException;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessage;
use RunAsRoot\MessageQueueRetry\Repository\Command\CreateMessageCommand;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteMessageByIdCommand;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteMessageCommand;
use RunAsRoot\MessageQueueRetry\Repository\Query\FindMessageByIdQuery;

class QueueErrorMessageRepository
{
    public function __construct(
        private CreateMessageCommand $createMessageCommand,
        private DeleteMessageCommand $deleteMessageCommand,
        private DeleteMessageByIdCommand $deleteMessageByIdCommand,
        private FindMessageByIdQuery $findMessageByIdQuery
    ) {
    }

    /**
     * @throws MessageNotFoundException
     */
    public function findById(int $id): QueueErrorMessage
    {
        return $this->findMessageByIdQuery->execute($id);
    }

    /**
     * @throws MessageCouldNotBeCreatedException
     */
    public function create(QueueErrorMessage $message): QueueErrorMessage
    {
        return $this->createMessageCommand->execute($message);
    }

    /**
     * @throws MessageCouldNotBeDeletedException
     */
    public function deleteById(int $id): void
    {
        $this->deleteMessageByIdCommand->execute($id);
    }

    /**
     * @throws MessageCouldNotBeDeletedException
     */
    public function delete(QueueErrorMessage $message): void
    {
        $this->deleteMessageCommand->execute($message);
    }
}
