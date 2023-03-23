<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Exception\MessageCouldNotBeDeletedException;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\MessageResource as ResourceModel;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteMessageByIdCommand;
use RunAsRoot\MessageQueueRetry\Repository\Query\FindMessageByIdQuery;

class DeleteMessageByIdCommandTest extends TestCase
{
    private DeleteMessageByIdCommand $sut;
    private ResourceModel|MockObject $resourceModelMock;
    private FindMessageByIdQuery|MockObject $findMessageByIdQueryMock;

    protected function setUp(): void
    {
        $this->resourceModelMock = $this->createMock(ResourceModel::class);
        $this->findMessageByIdQueryMock = $this->createMock(FindMessageByIdQuery::class);
        $this->sut = new DeleteMessageByIdCommand($this->resourceModelMock, $this->findMessageByIdQueryMock);
    }

    public function testExecute(): void
    {
        $entityId = 10;
        $messageMock = $this->createMock(Message::class);
        $this->findMessageByIdQueryMock->expects($this->once())->method('execute')->with($entityId)
            ->willReturn($messageMock);
        $this->resourceModelMock->expects($this->once())->method('delete')->with($messageMock);
        $this->sut->execute(10);
    }

    public function testExecuteWithException(): void
    {
        $this->expectException(MessageCouldNotBeDeletedException::class);
        $exception = new \Exception('test');
        $this->findMessageByIdQueryMock->expects($this->once())->method('execute')->willThrowException($exception);
        $this->sut->execute(10);
    }
}
