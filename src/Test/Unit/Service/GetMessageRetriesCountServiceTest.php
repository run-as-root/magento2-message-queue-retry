<?php

declare(strict_types = 1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Service;

use Magento\Framework\MessageQueue\Envelope;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Service\GetMessageRetriesCountService;

final class GetMessageRetriesCountServiceTest extends TestCase
{
    public function testItGetsMessageRetriesCount(): void
    {
        $testRetriesCount = 3;

        $messageMock = $this->createMock(Envelope::class);
        $applicationHeaders = new AMQPTable(['x-death' => [['count' => $testRetriesCount]]]);
        $topicName = 'sample_topic';
        $messageProperties = ['application_headers' => $applicationHeaders, 'topic_name' => $topicName];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $sut = new GetMessageRetriesCountService();
        $retriesCount = $sut->execute($messageMock);

        self::assertEquals($testRetriesCount, $retriesCount);
    }

    public function testItReturnsZeroAfterFirstProcessingBecauseItIsNotRetry(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $messageProperties = [ ];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $sut = new GetMessageRetriesCountService();
        $retriesCount = $sut->execute($messageMock);

        self::assertEquals(0, $retriesCount);
    }
}
