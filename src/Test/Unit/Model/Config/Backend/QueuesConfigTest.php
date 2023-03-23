<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Model\Config\Backend\QueuesConfig;
use RunAsRoot\MessageQueueRetry\Validator\QueueConfigurationValidator;

final class QueuesConfigTest extends TestCase
{
    private QueuesConfig $sut;
    private QueueConfigurationValidator|MockObject $queueConfigurationValidatorMock;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $eventManagerMock = $this->createMock(ManagerInterface::class);
        $contextMock->expects($this->once())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $registryMock = $this->createMock(Registry::class);
        $configMock = $this->createMock(ScopeConfigInterface::class);
        $cacheTypeListMock = $this->createMock(TypeListInterface::class);
        $abstractResourceMock = $this->createMock(AbstractResource::class);
        $abstractDbMock = $this->createMock(AbstractDb::class);
        $this->queueConfigurationValidatorMock = $this->createMock(QueueConfigurationValidator::class);
        $this->sut = new QueuesConfig(
            $contextMock,
            $registryMock,
            $configMock,
            $cacheTypeListMock,
            $this->queueConfigurationValidatorMock,
            $abstractResourceMock,
            $abstractDbMock
        );
    }

    public function testBeforeSave(): void
    {
        $this->sut->setValue([]);

        $this->queueConfigurationValidatorMock->expects($this->once())->method('validate')->with([]);

        $this->sut->beforeSave();
    }
}
