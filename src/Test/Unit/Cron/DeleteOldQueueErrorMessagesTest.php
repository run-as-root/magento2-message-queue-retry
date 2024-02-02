<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Cron;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Cron\DeleteOldQueueErrorMessages;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteOldMessagesCommand;

final class DeleteOldQueueErrorMessagesTest extends TestCase
{
    private DeleteOldQueueErrorMessages $sut;
    private DeleteOldMessagesCommand|MockObject $deleteOldMessagesCommandMock;

    protected function setUp(): void
    {
        $this->deleteOldMessagesCommandMock = $this->createMock(DeleteOldMessagesCommand::class);
        $this->sut = new DeleteOldQueueErrorMessages($this->deleteOldMessagesCommandMock);
    }

    public function testExecute(): void
    {
        $this->deleteOldMessagesCommandMock->expects($this->once())->method('execute');
        $this->sut->execute();
    }
}
