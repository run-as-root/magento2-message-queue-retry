<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Plugin;

use JsonException;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeCreatedException;
use RunAsRoot\MessageQueueRetry\Service\IsMessageShouldBeSavedForRetryService;
use RunAsRoot\MessageQueueRetry\Service\SaveFailedMessageService;

class HandleQueueMessageRejectPlugin
{
    public function __construct(
        private IsMessageShouldBeSavedForRetryService $isMessageShouldBeSavedForRetryService,
        private SaveFailedMessageService $saveFailedMessageService
    ) {
    }

    /**
     * @throws MessageCouldNotBeCreatedException
     * @throws JsonException
     */
    public function aroundReject(
        QueueInterface $subject,
        callable $proceed,
        EnvelopeInterface $envelope,
        bool $requeue = true,
        string $rejectionMessage = null
    ): void {
        if (!$rejectionMessage) {
            $proceed($envelope, $requeue, $rejectionMessage);
            return;
        }

        if (str_contains($rejectionMessage, 'MESSAGE_QUEUE_SKIP_RETRY')) {
            $subject->acknowledge($envelope);
            return;
        }

        $shouldBeSavedForRetry = $this->isMessageShouldBeSavedForRetryService->execute($envelope);

        if (!$shouldBeSavedForRetry) {
            $proceed($envelope, $requeue, $rejectionMessage);
            return;
        }

        $this->saveFailedMessageService->execute($envelope, $rejectionMessage);
        $subject->acknowledge($envelope);
    }
}
