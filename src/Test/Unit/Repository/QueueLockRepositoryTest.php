<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteQueueLockByIdCommand;
use RunAsRoot\MessageQueueRetry\Repository\QueueLockRepository;

class QueueLockRepositoryTest extends TestCase
{
    private QueueLockRepository $sut;
    private DeleteQueueLockByIdCommand|MockObject $deleteQueueLockByIdCommandMock;

    protected function setUp(): void
    {
        $this->deleteQueueLockByIdCommandMock = $this->createMock(DeleteQueueLockByIdCommand::class);
        $this->sut = new QueueLockRepository($this->deleteQueueLockByIdCommandMock);
    }

    public function testDeleteById(): void
    {
        $id = 10;
        $this->deleteQueueLockByIdCommandMock->expects($this->once())->method('execute')->with($id);
        $this->sut->deleteById($id);
    }
}
