<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Repository\Query;

use Magento\Framework\Config\DataInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Config\QueueRetryConfigInterface;
use RunAsRoot\MessageQueueRetry\Repository\Query\FindQueueRetryLimitByTopicNameQuery;

class FindQueueRetryLimitByTopicNameQueryTest extends TestCase
{
    private FindQueueRetryLimitByTopicNameQuery $sut;
    private DataInterface|MockObject $configStorageMock;

    protected function setUp(): void
    {
        $this->configStorageMock = $this->createMock(DataInterface::class);
        $this->sut = new FindQueueRetryLimitByTopicNameQuery($this->configStorageMock);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExecute($expected, $topicName, $config): void
    {
        $configKey = QueueRetryConfigInterface::CONFIG_KEY_NAME . '/' . $topicName;
        $this->configStorageMock->expects($this->once())->method('get')->with($configKey)->willReturn($config);

        $result = $this->sut->execute($topicName);

        $this->assertEquals($expected, $result);
    }

    public function dataProvider(): array
    {
        return [
            [3, 'sample_topic', ['retry_limit' => '3']],
            [null, 'sample_topic', ['some_key' => '3']],
            [null, 'sample_topic', null],
        ];
    }
}
