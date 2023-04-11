<?php

declare(strict_types = 1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Service;

use Magento\Framework\MessageQueue\Envelope;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Service\GetMessageRetriesCountService;
use RunAsRoot\MessageQueueRetry\Service\IsMessageShouldBeSavedForRetryService;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

final class IsMessageShouldBeSavedForRetryServiceTest extends TestCase
{
    private MessageQueueRetryConfig|MockObject $messageQueueRetryConfigMock;
    private GetMessageRetriesCountService|MockObject $getMessageRetriesCountServiceMock;
    private IsMessageShouldBeSavedForRetryService $sut;

    protected function setUp(): void
    {
        $this->messageQueueRetryConfigMock = $this->createMock(MessageQueueRetryConfig::class);
        $this->getMessageRetriesCountServiceMock = $this->createMock(GetMessageRetriesCountService::class);

        $this->messageQueueRetryConfigMock->method('isDelayQueueEnabled')->willReturn(true);

        $this->sut = new IsMessageShouldBeSavedForRetryService(
            $this->messageQueueRetryConfigMock,
            $this->getMessageRetriesCountServiceMock
        );
    }

    public function testItReturnsTrueIfRetryLimitIsReached(): void
    {
        $testMessageProperties = ['topic_name' => 'sample_topic'];
        $testQueueConfiguration = ['sample_topic' => ['retry_limit' => 2]];

        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(2);
        $this->messageQueueRetryConfigMock->expects($this->once())->method('getDelayQueues')->willReturn($testQueueConfiguration);

        $result = $this->sut->execute(new Envelope('', $testMessageProperties));
        $this->assertTrue($result);
    }

    public function testItReturnsFalseIfRetryLimitIsNotReached(): void
    {
        $testMessageProperties = ['topic_name' => 'sample_topic'];
        $testQueueConfiguration = ['sample_topic' => ['retry_limit' => 2]];

        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(1);
        $this->messageQueueRetryConfigMock->expects($this->once())->method('getDelayQueues')->willReturn($testQueueConfiguration);

        $result = $this->sut->execute(new Envelope('', $testMessageProperties));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfQueueConfigHasNoRetryLimit(): void
    {
        $testMessageProperties = ['topic_name' => 'sample_topic'];
        $testQueueConfiguration = ['sample_topic' => []];

        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(1);
        $this->messageQueueRetryConfigMock->expects($this->once())->method('getDelayQueues')->willReturn($testQueueConfiguration);

        $result = $this->sut->execute(new Envelope('', $testMessageProperties));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfQueueIsNotConfigured(): void
    {
        $testMessageProperties = ['topic_name' => 'sample_topic'];
        $testQueueConfiguration = ['another_topic' => ['retry_limit' => 1]];

        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(1);
        $this->messageQueueRetryConfigMock->expects($this->once())->method('getDelayQueues')->willReturn($testQueueConfiguration);

        $result = $this->sut->execute(new Envelope('', $testMessageProperties));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfMessageHasNoTopicName(): void
    {
        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(1);

        $result = $this->sut->execute(new Envelope('', []));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfItIsFirstTimeConsuming(): void
    {
        $this->getMessageRetriesCountServiceMock->expects($this->once())->method('execute')->willReturn(0);

        $result = $this->sut->execute(new Envelope('', []));
        $this->assertFalse($result);
    }

    public function testItReturnsFalseIfConfigDisabled(): void
    {
        $this->messageQueueRetryConfigMock->expects($this->once())
            ->method('isDelayQueueEnabled')
            ->willReturn(false);

        $result = $this->sut->execute(new Envelope('', []));
        $this->assertFalse($result);
    }
}
