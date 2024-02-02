<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Controller\Adminhtml\Message;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Message\Download;
use RunAsRoot\MessageQueueRetry\Mapper\QueueErrorMessageToRawResponseMapper;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessage;
use RunAsRoot\MessageQueueRetry\Repository\QueueErrorMessageRepository;

final class DownloadTest extends TestCase
{
    private Download $sut;
    private RawFactory|MockObject $rawFactoryMock;
    private QueueErrorMessageRepository|MockObject $messageRepositoryMock;
    private QueueErrorMessageToRawResponseMapper|MockObject $messageToRawResponseMapperMock;
    private RequestInterface|MockObject $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $this->rawFactoryMock = $this->createMock(RawFactory::class);
        $this->messageRepositoryMock = $this->createMock(QueueErrorMessageRepository::class);
        $this->messageToRawResponseMapperMock = $this->createMock(QueueErrorMessageToRawResponseMapper::class);
        $this->sut = new Download(
            $contextMock,
            $this->messageRepositoryMock,
            $this->rawFactoryMock,
            $this->messageToRawResponseMapperMock
        );
    }

    public function testExecute(): void
    {
        $this->requestMock->expects($this->once())->method('getParam')->with('message_id')->willReturn('10');

        $messageMock = $this->createMock(QueueErrorMessage::class);
        $this->messageRepositoryMock->expects($this->once())->method('findById')->with(10)->willReturn($messageMock);

        $rawMock = $this->createMock(Raw::class);
        $this->rawFactoryMock->expects($this->once())->method('create')->willReturn($rawMock);

        $this->messageToRawResponseMapperMock->expects($this->once())->method('map')->with($messageMock, $rawMock);
        $result = $this->sut->execute();

        $this->assertEquals($rawMock, $result);
    }

    public function testAdminResourceValue(): void
    {
        $this->assertEquals('RunAsRoot_MessageQueueRetry::download', Download::ADMIN_RESOURCE);
    }
}
