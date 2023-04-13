<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

final class MessageQueueRetryConfigTest extends TestCase
{
    private MessageQueueRetryConfig $sut;
    private ScopeConfigInterface|MockObject $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->sut = new MessageQueueRetryConfig($this->scopeConfigMock);
    }

    public function testIsDelayQueueEnabled(): void
    {
        $configPath = 'message_queue_retry/general/enable_delay_queue';
        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with($configPath)->willReturn(true);
        $this->assertTrue($this->sut->isDelayQueueEnabled());
    }
}
