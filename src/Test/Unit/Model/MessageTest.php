<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Model\Message;

final class MessageTest extends TestCase
{
    private Message $sut;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $registryMock = $this->createMock(Registry::class);
        $resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIdFieldName'])
            ->onlyMethods(['_construct', 'getConnection'])
            ->getMock();
        $resourceMock->expects($this->any())->method('getIdFieldName')->willReturn('id');
        $resourceCollectionMock = $this->createMock(AbstractDb::class);
        $this->sut = new Message(
            $contextMock,
            $registryMock,
            $resourceMock,
            $resourceCollectionMock
        );
    }

    public function testGettersAndSetters(): void
    {
        $this->sut->setTopicName('topic');
        $this->sut->setMessageBody('body');
        $this->sut->setFailureDescription('description');
        $this->sut->setResourceId('resource');
        $this->sut->setTotalRetries(1);

        $this->assertEquals('topic', $this->sut->getTopicName());
        $this->assertEquals('body', $this->sut->getMessageBody());
        $this->assertEquals('description', $this->sut->getFailureDescription());
        $this->assertEquals('resource', $this->sut->getResourceId());
        $this->assertEquals(1, $this->sut->getTotalRetries());
    }
}
