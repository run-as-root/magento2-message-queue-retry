<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteQueueLockByIdCommand;

class DeleteQueueLockByIdCommandTest extends TestCase
{
    private DeleteQueueLockByIdCommand $sut;
    private ResourceConnection|MockObject $resourceConnectionMock;

    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->sut = new DeleteQueueLockByIdCommand($this->resourceConnectionMock);
    }

    public function testExecute(): void
    {
        $id = 1;
        $connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);

        $tableName = 'queue_lock';
        $this->resourceConnectionMock->expects($this->once())->method('getTableName')->willReturn($tableName);
        $connectionMock->expects($this->once())->method('delete')->with($tableName, ['id = ?' => $id]);

        $this->sut->execute($id);
    }
}
