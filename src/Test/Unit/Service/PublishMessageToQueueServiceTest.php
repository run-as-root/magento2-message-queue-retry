<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Queue\Publisher;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;
use RunAsRoot\MessageQueueRetry\Service\PublishMessageToQueueService;

final class PublishMessageToQueueServiceTest extends TestCase
{
    private PublishMessageToQueueService $sut;
    private Publisher|MockObject $publisherMock;
    private MessageRepository|MockObject $messageRepositoryMock;

    protected function setUp(): void
    {
        $this->publisherMock = $this->createMock(Publisher::class);
        $this->messageRepositoryMock = $this->createMock(MessageRepository::class);
        $this->sut = new PublishMessageToQueueService($this->publisherMock, $this->messageRepositoryMock);
    }

    public function testExecuteById(): void
    {
        $messageId = 1;
        $messageMock = $this->createMock(Message::class);
        $this->messageRepositoryMock->expects($this->once())->method('findById')->with($messageId)
            ->willReturn($messageMock);
        $topicName = 'topic.name';
        $messageBody = 'message body';
        $messageMock->expects($this->once())->method('getTopicName')->willReturn($topicName);
        $messageMock->expects($this->once())->method('getMessageBody')->willReturn($messageBody);
        $this->publisherMock->expects($this->once())->method('publish')->with($topicName, $messageBody);
        $this->messageRepositoryMock->expects($this->once())->method('delete')->with($messageMock);

        $this->sut->executeById($messageId);
    }

    public function testExecuteByMessage(): void
    {
        $messageMock = $this->createMock(Message::class);
        $topicName = 'topic.name';
        $messageBody = 'message body';
        $messageMock->expects($this->once())->method('getTopicName')->willReturn($topicName);
        $messageMock->expects($this->once())->method('getMessageBody')->willReturn($messageBody);
        $this->publisherMock->expects($this->once())->method('publish')->with($topicName, $messageBody);
        $this->messageRepositoryMock->expects($this->once())->method('delete')->with($messageMock);

        $this->sut->executeByMessage($messageMock);
    }
}
