<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Service;

use RunAsRoot\MessageQueueRetry\Exception\InvalidPublisherConfigurationException;
use RunAsRoot\MessageQueueRetry\Exception\InvalidQueueConnectionTypeException;
use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeDeletedException;
use RunAsRoot\MessageQueueRetry\Exception\MessageNotFoundException;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessage;
use RunAsRoot\MessageQueueRetry\Queue\Publisher;
use RunAsRoot\MessageQueueRetry\Repository\QueueErrorMessageRepository;

class PublishMessageToQueueService
{
    public function __construct(
        private Publisher $publisher,
        private QueueErrorMessageRepository $messageRepository
    ) {
    }

    /**
     * @throws MessageCouldNotBeDeletedException
     * @throws MessageNotFoundException
     * @throws InvalidQueueConnectionTypeException
     * @throws InvalidPublisherConfigurationException
     */
    public function executeById(int $messageId): void
    {
        $message = $this->messageRepository->findById($messageId);
        $this->publisher->publish($message->getTopicName(), $message->getMessageBody());
        $this->messageRepository->delete($message);
    }

    /**
     * @throws MessageCouldNotBeDeletedException
     * @throws MessageNotFoundException
     * @throws InvalidQueueConnectionTypeException
     * @throws InvalidPublisherConfigurationException
     */
    public function executeByMessage(QueueErrorMessage $message): void
    {
        $this->publisher->publish($message->getTopicName(), $message->getMessageBody());
        $this->messageRepository->delete($message);
    }
}
