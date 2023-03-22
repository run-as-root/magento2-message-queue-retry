<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeCreatedException;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Repository\Command\CreateMessageCommand;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\MessageResource as ResourceModel;

class CreateMessageCommandTest extends TestCase
{
    private CreateMessageCommand $sut;
    private ResourceModel|MockObject $resourceModelMock;

    protected function setUp(): void
    {
        $this->resourceModelMock = $this->createMock(ResourceModel::class);
        $this->sut = new CreateMessageCommand($this->resourceModelMock);
    }

    public function testExecute(): void
    {
        $messageMock = $this->createMock(Message::class);
        $this->resourceModelMock->expects($this->once())->method('save')->with($messageMock);
        $result = $this->sut->execute($messageMock);
        $this->assertEquals($messageMock, $result);
    }

    public function testExecuteWithException(): void
    {
        $this->expectException(MessageCouldNotBeCreatedException::class);
        $messageMock = $this->createMock(Message::class);
        $this->resourceModelMock->expects($this->once())->method('save')->with($messageMock)
            ->willThrowException(new \Exception('test'));
        $this->sut->execute($messageMock);
    }
}
