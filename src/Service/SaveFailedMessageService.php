<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Service;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeCreatedException;
use RunAsRoot\MessageQueueRetry\Model\MessageFactory;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;

class SaveFailedMessageService
{
    public function __construct(
        private MessageFactory $messageFactory,
        private MessageRepository $messageRepository,
        private GetMessageRetriesCountService $getMessageRetriesCountService
    ) {
    }

    /**
     * @throws MessageCouldNotBeCreatedException
     */
    public function execute(EnvelopeInterface $message, string $exceptionMessage): void
    {
        $messageProperties = $message->getProperties();

        $messageModel = $this->messageFactory->create();

        $messageModel->setTopicName($messageProperties['topic_name']);
        $messageModel->setMessageBody($message->getBody());
        $messageModel->setFailureDescription($exceptionMessage);
        $messageModel->setTotalRetries($this->getMessageRetriesCountService->execute($message));

        $this->messageRepository->create($messageModel);
    }
}
