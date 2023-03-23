<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Controller\Adminhtml\Message;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Message\Requeue;
use RunAsRoot\MessageQueueRetry\Service\PublishMessageToQueueService;

final class RequeueTest extends TestCase
{
    private Requeue $sut;
    private RedirectFactory|MockObject $redirectFactoryMock;
    private PublishMessageToQueueService|MockObject $publishMessageToQueueServiceMock;
    private MessageManagerInterface|MockObject $messageManagerMock;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);
        $contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->publishMessageToQueueServiceMock = $this->createMock(PublishMessageToQueueService::class);

        $this->sut = new Requeue(
            $contextMock,
            $this->publishMessageToQueueServiceMock,
            $this->redirectFactoryMock
        );
    }

    public function testExecute(): void
    {
        $this->requestMock->expects($this->once())->method('getParam')->with('message_id')->willReturn('10');

        $redirectMock = $this->createMock(Redirect::class);
        $this->redirectFactoryMock->expects($this->once())->method('create')->willReturn($redirectMock);

        $this->publishMessageToQueueServiceMock->expects($this->once())->method('executeById')->with(10);

        $message = __('Message queued successfully');
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage')->with($message);

        $redirectMock->expects($this->once())->method('setPath')->with('message_queue_retry/index/index');

        $result = $this->sut->execute();

        $this->assertEquals($redirectMock, $result);
    }

    public function testExecuteWithInvalidMessageId(): void
    {
        $this->requestMock->expects($this->once())->method('getParam')->with('message_id')->willReturn(null);

        $redirectMock = $this->createMock(Redirect::class);
        $this->redirectFactoryMock->expects($this->once())->method('create')->willReturn($redirectMock);
        $redirectMock->expects($this->once())->method('setPath')->with('message_queue_retry/index/index');

        $message = __('Invalid message id provided in the request params');
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage')->with($message);

        $this->publishMessageToQueueServiceMock->expects($this->never())->method('executeById');

        $result = $this->sut->execute();

        $this->assertEquals($redirectMock, $result);
    }

    public function testAdminResourceValue(): void
    {
        $this->assertEquals('RunAsRoot_MessageQueueRetry::requeue', Requeue::ADMIN_RESOURCE);
    }
}
