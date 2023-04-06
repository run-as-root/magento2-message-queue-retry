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

    public function testGetDelayQueues(): void
    {
        $configPath = 'message_queue_retry/general/delay_queues';
        $configValues = $this->getDelayQueuesJson();
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with($configPath)->willReturn($configValues);

        $result = $this->sut->getDelayQueues();

        $expected = [
            'sample_topic' => [
                'main_topic_name' => 'sample_topic',
                'delay_topic_name' => 'sample_topic_delay',
                'retry_limit' => 3,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    private function getDelayQueuesJson(): string
    {
        return trim(file_get_contents(__DIR__ . '/_files/delay_queues_config.json'));
    }
}
