<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Repository\Command\CreateMessageCommand;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteMessageByIdCommand;
use RunAsRoot\MessageQueueRetry\Repository\Command\DeleteMessageCommand;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;
use RunAsRoot\MessageQueueRetry\Repository\Query\FindMessageByIdQuery;

class MessageRepositoryTest extends TestCase
{
    private MessageRepository $sut;
    private CreateMessageCommand|MockObject $createMessageCommandMock;
    private DeleteMessageCommand|MockObject $deleteMessageCommandMock;
    private DeleteMessageByIdCommand|MockObject $deleteMessageByIdCommandMock;
    private FindMessageByIdQuery|MockObject $findMessageByIdQueryMock;

    protected function setUp(): void
    {
        $this->createMessageCommandMock = $this->createMock(CreateMessageCommand::class);
        $this->deleteMessageCommandMock = $this->createMock(DeleteMessageCommand::class);
        $this->deleteMessageByIdCommandMock = $this->createMock(DeleteMessageByIdCommand::class);
        $this->findMessageByIdQueryMock = $this->createMock(FindMessageByIdQuery::class);
        $this->sut = new MessageRepository(
            $this->createMessageCommandMock,
            $this->deleteMessageCommandMock,
            $this->deleteMessageByIdCommandMock,
            $this->findMessageByIdQueryMock
        );
    }

    public function testFindById(): void
    {
        $id = 10;
        $this->findMessageByIdQueryMock->expects($this->once())->method('execute')->with($id);
        $this->sut->findById($id);
    }

    public function testCreate(): void
    {
        $messageMock = $this->createMock(Message::class);
        $this->createMessageCommandMock->expects($this->once())->method('execute')->with($messageMock);
        $this->sut->create($messageMock);
    }

    public function testDeleteById(): void
    {
        $id = 10;
        $this->deleteMessageByIdCommandMock->expects($this->once())->method('execute')->with($id);
        $this->sut->deleteById($id);
    }

    public function testDelete(): void
    {
        $messageMock = $this->createMock(Message::class);
        $this->deleteMessageCommandMock->expects($this->once())->method('execute')->with($messageMock);
        $this->sut->delete($messageMock);
    }
}
