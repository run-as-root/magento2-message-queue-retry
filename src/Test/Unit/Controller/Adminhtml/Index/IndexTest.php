<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Index\Index;

final class IndexTest extends TestCase
{
    private Index $sut;
    private PageFactory|MockObject $pageFactoryMock;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $this->pageFactoryMock = $this->createMock(PageFactory::class);
        $this->sut = new Index($contextMock, $this->pageFactoryMock);
    }

    public function testExecute(): void
    {
        $pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->addMethods(['setActiveMenu'])
            ->onlyMethods(['getConfig'])
            ->getMock();

        $this->pageFactoryMock->expects($this->once())->method('create')->willReturn($pageMock);
        $pageConfigMock = $this->createMock(Config::class);
        $pageMock->expects($this->once())->method('getConfig')->willReturn($pageConfigMock);
        $pageTitleMock = $this->createMock(Title::class);
        $pageConfigMock->expects($this->once())->method('getTitle')->willReturn($pageTitleMock);
        $pageTitleMock->expects($this->once())->method('prepend')->with(__('Messages'));
        $pageMock->expects($this->once())->method('setActiveMenu')->with('RunAsRoot_MessageQueueRetry::listing');

        $result = $this->sut->execute();

        $this->assertEquals($pageMock, $result);
    }

    public function testAdminResourceValue(): void
    {
        $this->assertEquals('RunAsRoot_MessageQueueRetry::listing', Index::ADMIN_RESOURCE);
    }
}
