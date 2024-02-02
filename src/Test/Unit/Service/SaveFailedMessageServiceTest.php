<?php

declare(strict_types = 1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Service;

use Magento\Framework\MessageQueue\Envelope;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessage;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessageFactory;
use RunAsRoot\MessageQueueRetry\Repository\QueueErrorMessageRepository;
use RunAsRoot\MessageQueueRetry\Service\GetMessageRetriesCountService;
use RunAsRoot\MessageQueueRetry\Service\SaveFailedMessageService;

final class SaveFailedMessageServiceTest extends TestCase
{
    public function testItSavesFailedMessage(): void
    {
        $messageFactoryMock = $this->createMock(QueueErrorMessageFactory::class);
        $messageRepoMock = $this->createMock(QueueErrorMessageRepository::class);
        $getMessageRetriesCountServiceMock = $this->createMock(GetMessageRetriesCountService::class);
        $messageMock = $this->createMock(QueueErrorMessage::class);

        $testRetriesCount = 3;
        $testExceptionMessage = 'some exception message';
        $testMessageBody = 'body';
        $testTopicName = 'some_topic_name';
        $testMessageProperties = [ 'topic_name' => $testTopicName ];
        $testMessage = new Envelope($testMessageBody, $testMessageProperties);

        $messageFactoryMock->method('create')->willReturn($messageMock);
        $getMessageRetriesCountServiceMock->expects($this->once())
            ->method('execute')
            ->with($testMessage)
            ->willReturn($testRetriesCount);

        $messageMock->expects($this->once())->method('setTopicName')->with($testTopicName);
        $messageMock->expects($this->once())->method('setMessageBody')->with($testMessageBody);
        $messageMock->expects($this->once())->method('setFailureDescription')->with($testExceptionMessage);
        $messageMock->expects($this->once())->method('setTotalRetries')->with($testRetriesCount);

        $messageRepoMock->expects($this->once())->method('create')->with($messageMock);

        $sut = new SaveFailedMessageService($messageFactoryMock, $messageRepoMock, $getMessageRetriesCountServiceMock);
        $sut->execute($testMessage, $testExceptionMessage);
    }
}
