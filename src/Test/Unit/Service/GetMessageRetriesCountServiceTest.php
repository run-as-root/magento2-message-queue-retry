<?php

declare(strict_types = 1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Service;

use Magento\Framework\MessageQueue\Envelope;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Service\GetMessageRetriesCountService;

final class GetMessageRetriesCountServiceTest extends TestCase
{
    private GetMessageRetriesCountService $sut;

    protected function setUp(): void
    {
        $this->sut = new GetMessageRetriesCountService();
    }

    public function testItGetsMessageRetriesCount(): void
    {
        $testRetriesCount = 3;

        $messageMock = $this->createMock(Envelope::class);
        $applicationHeaders = new AMQPTable(['x-death' => [['count' => $testRetriesCount]]]);
        $topicName = 'sample_topic';
        $messageProperties = ['application_headers' => $applicationHeaders, 'topic_name' => $topicName];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $retriesCount = $this->sut->execute($messageMock);

        $this->assertEquals($testRetriesCount, $retriesCount);
    }

    public function testItReturnsZeroAfterFirstProcessingBecauseItIsNotRetry(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $messageProperties = [ ];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $retriesCount = $this->sut->execute($messageMock);

        $this->assertEquals(0, $retriesCount);
    }

    public function testItReturnsZeroAsFallback(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $applicationHeaders = new AMQPTable(['x-death' => [['count' => null]]]);
        $topicName = 'sample_topic';
        $messageProperties = ['application_headers' => $applicationHeaders, 'topic_name' => $topicName];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $retriesCount = $this->sut->execute($messageMock);

        $this->assertEquals(0, $retriesCount);
    }
}
