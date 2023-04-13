<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Converter;

use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Converter\QueueRetryXmlToArrayConverter;

final class QueueRetryXmlToArrayConverterTest extends TestCase
{
    private QueueRetryXmlToArrayConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new QueueRetryXmlToArrayConverter();
    }

    public function testConvert(): void
    {
        $doc = new \DOMDocument();
        $doc->loadXML($this->getQueueRetryXmlFile());

        $result = $this->sut->convert($doc);

        $expected = [
            'queue_retry_topics' => [
                'sample_topic' => [
                    'topic_name' => 'sample_topic',
                    'retry_limit' => 3,
                ],
                'another_topic' => [
                    'topic_name' => 'another_topic',
                    'retry_limit' => 10,
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function getQueueRetryXmlFile(): string
    {
        return file_get_contents(__DIR__ . '/_files/queue_retry.xml');
    }
}
