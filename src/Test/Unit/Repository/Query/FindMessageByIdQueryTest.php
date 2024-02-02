<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository\Query;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Exception\MessageNotFoundException;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessage;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessageFactory as ModelFactory;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\QueueErrorMessageResource as ResourceModel;
use RunAsRoot\MessageQueueRetry\Repository\Query\FindMessageByIdQuery;

class FindMessageByIdQueryTest extends TestCase
{
    private FindMessageByIdQuery $sut;
    private ResourceModel|MockObject $resourceModelMock;
    private ModelFactory|MockObject $modelFactoryMock;

    protected function setUp(): void
    {
        $this->resourceModelMock = $this->createMock(ResourceModel::class);
        $this->modelFactoryMock = $this->createMock(ModelFactory::class);
        $this->sut = new FindMessageByIdQuery($this->resourceModelMock, $this->modelFactoryMock);
    }

    public function testExecute(): void
    {
        $entityId = 1;
        $messageMock = $this->createMock(QueueErrorMessage::class);
        $this->modelFactoryMock->expects($this->once())->method('create')->willReturn($messageMock);
        $this->resourceModelMock->expects($this->once())->method('load')->with($messageMock, $entityId);
        $messageMock->expects($this->once())->method('getId')->willReturn($entityId);

        $result = $this->sut->execute($entityId);

        $this->assertEquals($messageMock, $result);
    }

    public function testExecuteWithException(): void
    {
        $entityId = 1;
        $messageMock = $this->createMock(QueueErrorMessage::class);
        $this->modelFactoryMock->expects($this->once())->method('create')->willReturn($messageMock);
        $this->resourceModelMock->expects($this->once())->method('load')->with($messageMock, $entityId);
        $messageMock->expects($this->once())->method('getId')->willReturn(null);
        $this->expectException(MessageNotFoundException::class);

        $this->sut->execute($entityId);
    }
}
