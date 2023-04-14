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
        $configKey = QueueRetryConfigInterface::CONFIG_KEY_NAME;
        $this->configStorageMock->expects($this->once())->method('get')->with($configKey)->willReturn($config);

        $result = $this->sut->execute($topicName);

        $this->assertEquals($expected, $result);
    }

    public function dataProvider(): array
    {
        return [
            [
                5,
                'sample_topic',
                [
                    'sample_topic' => ['retry_limit' => '5'],
                    'sample_topic2' => ['retry_limit' => '3'],
                    'sample_topic3' => ['retry_limit' => '8']
                ],
            ],
            [
                3,
                'sample_topic2',
                [
                    'sample_topic' => ['retry_limit' => '5'],
                    'sample_topic2' => ['retry_limit' => '3'],
                    'sample_topic3' => ['retry_limit' => '8']
                ],
            ],
            [
                null,
                'sample_topic2',
                [
                    'sample_topic' => ['retry_limit' => '5'],
                ],
            ],
            [
                null,
                'sample_topic2',
                null,
            ],
        ];
    }
}
