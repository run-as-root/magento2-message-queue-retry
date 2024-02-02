<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Api\Data\QueueErrorMessageInterface;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteOldMessagesCommand;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

final class DeleteOldMessagesCommandTest extends TestCase
{
    private DeleteOldMessagesCommand $sut;
    private ResourceConnection|MockObject $resourceConnectionMock;
    private MessageQueueRetryConfig|MockObject $messageQueueRetryConfigMock;

    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->messageQueueRetryConfigMock = $this->createMock(MessageQueueRetryConfig::class);
        $this->sut = new DeleteOldMessagesCommand($this->resourceConnectionMock, $this->messageQueueRetryConfigMock);
    }

    public function testExecute(): void
    {
        $tableName = QueueErrorMessageInterface::TABLE_NAME;
        $daysToKeepMessages = 30;
        $adapterMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $this->resourceConnectionMock->expects($this->once())->method('getTableName')
            ->with($tableName)->willReturn($tableName);

        $this->messageQueueRetryConfigMock->expects($this->once())
            ->method('getTotalDaysToKeepMessages')->willReturn($daysToKeepMessages);

        $adapterMock->expects($this->once())->method('delete')
            ->with($tableName, ["created_at < DATE_SUB(NOW(), INTERVAL $daysToKeepMessages DAY)"]);

        $this->sut->execute();
    }
}
