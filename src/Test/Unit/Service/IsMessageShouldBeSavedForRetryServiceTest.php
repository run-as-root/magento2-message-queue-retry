<?php

declare(strict_types = 1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Service;

use Magento\Framework\MessageQueue\Envelope;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Repository\Query\FindQueueRetryLimitByTopicNameQuery;
use RunAsRoot\MessageQueueRetry\Service\GetMessageRetriesCountService;
use RunAsRoot\MessageQueueRetry\Service\IsMessageShouldBeSavedForRetryService;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

final class IsMessageShouldBeSavedForRetryServiceTest extends TestCase
{
    private IsMessageShouldBeSavedForRetryService $sut;
    private MessageQueueRetryConfig|MockObject $messageQueueRetryConfigMock;
    private GetMessageRetriesCountService|MockObject $getMessageRetriesCountServiceMock;
    private FindQueueRetryLimitByTopicNameQuery|MockObject $findQueueRetryLimitByTopicNameQueryMock;

    protected function setUp(): void
    {
        $this->messageQueueRetryConfigMock = $this->createMock(MessageQueueRetryConfig::class);
        $this->getMessageRetriesCountServiceMock = $this->createMock(GetMessageRetriesCountService::class);
        $this->findQueueRetryLimitByTopicNameQueryMock = $this->createMock(FindQueueRetryLimitByTopicNameQuery::class);

        $this->sut = new IsMessageShouldBeSavedForRetryService(
            $this->messageQueueRetryConfigMock,
            $this->getMessageRetriesCountServiceMock,
            $this->findQueueRetryLimitByTopicNameQueryMock
        );
    }

    public function testItReturnsTrueIfRetryLimitIsReached(): void
    {
        $testMessageProperties = ['topic_name' => 'sample_topic'];

        $this->messageQueueRetryConfigMock->method('isDelayQueueEnabled')->willReturn(true);
        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(2);
        $this->findQueueRetryLimitByTopicNameQueryMock->expects($this->once())->method('execute')
            ->with('sample_topic')->willReturn(2);

        $result = $this->sut->execute(new Envelope('', $testMessageProperties));
        $this->assertTrue($result);
    }

    public function testItReturnsFalseIfRetryLimitIsNotReached(): void
    {
        $testMessageProperties = ['topic_name' => 'sample_topic'];

        $this->messageQueueRetryConfigMock->method('isDelayQueueEnabled')->willReturn(true);
        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(1);
        $this->findQueueRetryLimitByTopicNameQueryMock->expects($this->once())->method('execute')
            ->with('sample_topic')->willReturn(2);

        $result = $this->sut->execute(new Envelope('', $testMessageProperties));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfQueueConfigHasNoRetryLimit(): void
    {
        $testMessageProperties = ['topic_name' => 'sample_topic'];

        $this->messageQueueRetryConfigMock->method('isDelayQueueEnabled')->willReturn(true);
        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(1);
        $this->findQueueRetryLimitByTopicNameQueryMock->expects($this->once())->method('execute')
            ->with('sample_topic')->willReturn(null);

        $result = $this->sut->execute(new Envelope('', $testMessageProperties));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfMessageHasNoTopicName(): void
    {
        $this->messageQueueRetryConfigMock->method('isDelayQueueEnabled')->willReturn(true);
        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(1);

        $result = $this->sut->execute(new Envelope('', []));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfItIsFirstTimeConsuming(): void
    {
        $this->messageQueueRetryConfigMock->method('isDelayQueueEnabled')->willReturn(true);
        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(0);

        $result = $this->sut->execute(new Envelope('', []));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfConfigDisabled(): void
    {
        $this->messageQueueRetryConfigMock->expects($this->once())->method('isDelayQueueEnabled')->willReturn(false);

        $result = $this->sut->execute(new Envelope('', []));
        $this->assertFalse($result);
    }
}
