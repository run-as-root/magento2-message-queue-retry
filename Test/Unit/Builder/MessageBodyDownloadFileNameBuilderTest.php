<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Builder;

use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Builder\MessageBodyDownloadFileNameBuilder;
use RunAsRoot\MessageQueueRetry\Model\Message;

final class MessageBodyDownloadFileNameBuilderTest extends TestCase
{
    private MessageBodyDownloadFileNameBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = new MessageBodyDownloadFileNameBuilder();
    }

    public function testBuild(): void
    {
        $messageMock = $this->createMock(Message::class);
        $messageMock->expects($this->once())->method('getTopicName')->willReturn('topic_name');
        $messageMock->expects($this->once())->method('getId')->willReturn('message_id');

        $this->assertSame('topic_name_message_id.json', $this->sut->build($messageMock));
    }
}
