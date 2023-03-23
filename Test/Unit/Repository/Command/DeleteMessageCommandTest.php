<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeDeletedException;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\MessageResource as ResourceModel;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteMessageCommand;

class DeleteMessageCommandTest extends TestCase
{
    private DeleteMessageCommand $sut;
    private ResourceModel|MockObject $resourceModelMock;

    protected function setUp(): void
    {
        $this->resourceModelMock = $this->createMock(ResourceModel::class);
        $this->sut = new DeleteMessageCommand($this->resourceModelMock);
    }

    public function testExecute(): void
    {
        $messageMock = $this->createMock(Message::class);
        $this->resourceModelMock->expects($this->once())->method('delete')->with($messageMock);
        $this->sut->execute($messageMock);
    }

    public function testExecuteWithException(): void
    {
        $this->expectException(MessageCouldNotBeDeletedException::class);
        $messageMock = $this->createMock(Message::class);
        $this->resourceModelMock->expects($this->once())->method('delete')->with($messageMock)
            ->willThrowException(new \Exception('test'));
        $this->sut->execute($messageMock);
    }
}
