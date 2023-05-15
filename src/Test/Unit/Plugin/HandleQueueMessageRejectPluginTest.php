<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Plugin;

use Magento\Framework\MessageQueue\Envelope;
use Magento\Framework\MessageQueue\QueueInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Plugin\HandleQueueMessageRejectPlugin;
use RunAsRoot\MessageQueueRetry\Service\IsMessageShouldBeSavedForRetryService;
use RunAsRoot\MessageQueueRetry\Service\SaveFailedMessageService;

final class HandleQueueMessageRejectPluginTest extends TestCase
{
    private IsMessageShouldBeSavedForRetryService|MockObject $isMessageShouldBeSavedForRetryServiceMock;
    private SaveFailedMessageService|MockObject $saveFailedMessageServiceMock;
    private QueueInterface|MockObject $queueMock;
    private HandleQueueMessageRejectPlugin $sut;
    private bool $isProceedCalled;
    private \Closure $testProceedFn;

    public function setUp(): void
    {
        $this->isMessageShouldBeSavedForRetryServiceMock = $this->createMock(IsMessageShouldBeSavedForRetryService::class);
        $this->saveFailedMessageServiceMock = $this->createMock(SaveFailedMessageService::class);
        $this->queueMock = $this->createMock(QueueInterface::class);

        $this->isProceedCalled = false;
        $this->testProceedFn = fn () => $this->isProceedCalled = true;

        $this->sut = new HandleQueueMessageRejectPlugin(
            $this->isMessageShouldBeSavedForRetryServiceMock,
            $this->saveFailedMessageServiceMock
        );
    }

    public function testItShouldSaveFailedMessageAndNotProceed(): void
    {
        $testError = 'test error';

        $this->isMessageShouldBeSavedForRetryServiceMock->expects($this->once())->method('execute')->willReturn(true);
        $this->saveFailedMessageServiceMock->expects($this->once())->method('execute');
        $this->queueMock->expects($this->once())->method('acknowledge');

        $this->sut->aroundReject(
            $this->queueMock,
            $this->testProceedFn,
            new Envelope('body'),
            false,
            $testError
        );

        self::assertFalse($this->isProceedCalled);
    }

    public function testItProceedIfMessageShouldNotBeSavedForRetry(): void
    {
        $testError = 'test error';

        $this->isMessageShouldBeSavedForRetryServiceMock->expects($this->once())->method('execute')->willReturn(false);
        $this->saveFailedMessageServiceMock->expects($this->never())->method('execute');

        $this->sut->aroundReject(
            $this->queueMock,
            $this->testProceedFn,
            new Envelope('body'),
            false,
            $testError
        );

        self::assertTrue($this->isProceedCalled);
    }

    public function testItProceedIfThereIsNoError(): void
    {
        $testEmptyError = '';

        $this->saveFailedMessageServiceMock->expects($this->never())->method('execute');
        $this->isMessageShouldBeSavedForRetryServiceMock->expects($this->never())->method('execute');

        $this->sut->aroundReject(
            $this->queueMock,
            $this->testProceedFn,
            new Envelope('body'),
            false,
            $testEmptyError
        );

        self::assertTrue($this->isProceedCalled);
    }

    /**
     * @dataProvider skipRetryDataProvider
     */
    public function testItShouldSkipRetryWhenExceptionMessageContainsAnUniqueIdentifier(string $exceptionMessage): void
    {
        $this->saveFailedMessageServiceMock->expects($this->never())->method('execute');
        $this->isMessageShouldBeSavedForRetryServiceMock->expects($this->never())->method('execute');

        $this->sut->aroundReject(
            $this->queueMock,
            $this->testProceedFn,
            new Envelope('body'),
            false,
            $exceptionMessage
        );

        self::assertTrue($this->isProceedCalled);
    }

    public function skipRetryDataProvider(): array
    {
        return [
            ['Some Error MESSAGE_QUEUE_SKIP_RETRY'],
            ['MESSAGE_QUEUE_SKIP_RETRY'],
            ['Some Error MESSAGE_QUEUE_SKIP_RETRY Some Error'],
            ['Some Error MESSAGE_QUEUE_SKIP_RETRY Some Error MESSAGE_QUEUE_SKIP_RETRY'],
        ];
    }
}
