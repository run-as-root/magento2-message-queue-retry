<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Controller\Adminhtml\Message;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Message\MassDelete;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\Message\MessageCollection;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\Message\CollectionFactory;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;

final class MassDeleteTest extends TestCase
{
    private MassDelete $sut;
    private RedirectFactory|MockObject $redirectFactoryMock;
    private MessageRepository|MockObject $messageRepositoryMock;
    private CollectionFactory|MockObject $collectionFactoryMock;
    private Filter|MockObject $filterMock;
    private MessageManagerInterface|MockObject $messageManagerMock;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);
        $contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->messageRepositoryMock = $this->createMock(MessageRepository::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->filterMock = $this->createMock(Filter::class);

        $this->sut = new MassDelete(
            $contextMock,
            $this->messageRepositoryMock,
            $this->redirectFactoryMock,
            $this->collectionFactoryMock,
            $this->filterMock,
        );
    }

    public function testExecute(): void
    {
        $redirectMock = $this->createMock(Redirect::class);
        $this->redirectFactoryMock->expects($this->once())->method('create')->willReturn($redirectMock);

        $messageOneMock = $this->createMock(Message::class);
        $messageTwoMock = $this->createMock(Message::class);
        $collectionMock = $this->createMock(MessageCollection::class);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$messageOneMock, $messageTwoMock]);

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $this->filterMock->expects($this->once())->method('getCollection')->with($collectionMock)
            ->willReturn($collectionMock);

        $this->messageRepositoryMock->expects($this->exactly(2))->method('delete')
            ->withConsecutive([$messageOneMock], [$messageTwoMock]);

        $message = __('The messages have been successfully deleted');
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage')->with($message);

        $redirectMock->expects($this->once())->method('setPath')->with('message_queue_retry/index/index');

        $result = $this->sut->execute();

        $this->assertEquals($redirectMock, $result);
    }

    public function testAdminResourceValue(): void
    {
        $this->assertEquals('RunAsRoot_MessageQueueRetry::mass_delete', MassDelete::ADMIN_RESOURCE);
    }
}
