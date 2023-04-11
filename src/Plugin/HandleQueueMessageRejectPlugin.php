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
        bool $requeue,
        string $error
    ): void {
        if (!$error) {
            $proceed($envelope, $requeue, $error);
        }

        $shouldBeSavedForRetry = $this->isMessageShouldBeSavedForRetryService->execute($envelope);

        if (!$shouldBeSavedForRetry) {
            $proceed($envelope, $requeue, $error);
            return;
        }

        $this->saveFailedMessageService->execute($envelope, $error);
        $subject->acknowledge($envelope);
    }
}
