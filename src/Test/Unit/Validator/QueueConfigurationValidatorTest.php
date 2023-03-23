<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Exception\InvalidQueueConfigurationException;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig as ConfigFieldNames;
use RunAsRoot\MessageQueueRetry\Validator\QueueConfigurationValidator;

final class QueueConfigurationValidatorTest extends TestCase
{
    private QueueConfigurationValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new QueueConfigurationValidator();
    }

    /**
     * @dataProvider validConfigDataProvider
     */
    public function testValidConfig(array $configValues): void
    {
        $this->assertTrue($this->sut->validate($configValues));
    }

    /**
     * @dataProvider invalidConfigDataProvider
     */
    public function testInvalidConfig(array $configValues): void
    {
        $this->expectException(InvalidQueueConfigurationException::class);

        $this->sut->validate($configValues);
    }

    public function validConfigDataProvider(): array
    {
        return [
            [
                [
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => 'topic1',
                        ConfigFieldNames::DELAY_TOPIC_NAME => 'topic2',
                    ],
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => 'topic3',
                        ConfigFieldNames::DELAY_TOPIC_NAME => null,
                    ],
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => null,
                        ConfigFieldNames::DELAY_TOPIC_NAME => 'topic4',
                    ],
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => null,
                        ConfigFieldNames::DELAY_TOPIC_NAME => null,
                    ],
                ],
            ],
        ];
    }

    public function invalidConfigDataProvider(): array
    {
        return [
            [
                [
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => 'topic1',
                        ConfigFieldNames::DELAY_TOPIC_NAME => 'topic2',
                    ],
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => 'topic1',
                        ConfigFieldNames::DELAY_TOPIC_NAME => 'topic3',
                    ],
                ]
            ],
            [
                [
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => 'topic1',
                        ConfigFieldNames::DELAY_TOPIC_NAME => 'topic2',
                    ],
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => 'topic3',
                        ConfigFieldNames::DELAY_TOPIC_NAME => 'topic2',
                    ],
                ]
            ],
            [
                [
                    [
                        ConfigFieldNames::MAIN_TOPIC_NAME => 'topic1',
                        ConfigFieldNames::DELAY_TOPIC_NAME => 'topic1',
                    ],
                ]
            ],
        ];
    }
}
