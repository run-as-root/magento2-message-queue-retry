<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Mapper;

use Magento\Framework\Controller\Result\Raw as RawResponse;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Builder\MessageBodyDownloadFileNameBuilder;
use RunAsRoot\MessageQueueRetry\Exception\EmptyQueueMessageBodyException;
use RunAsRoot\MessageQueueRetry\Mapper\MessageToRawResponseMapper;
use RunAsRoot\MessageQueueRetry\Model\Message;

final class MessageToRawResponseMapperTest extends TestCase
{
    private MessageToRawResponseMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new MessageToRawResponseMapper(new MessageBodyDownloadFileNameBuilder());
    }

    public function testMap(): void
    {
        $messageMock = $this->createMock(Message::class);
        $rawResponseMock = $this->createMock(RawResponse::class);

        $messageBody = '{"test": "test"}';
        $contentLength = strlen($messageBody);
        $messageMock->expects($this->once())->method('getMessageBody')->willReturn($messageBody);
        $messageMock->expects($this->once())->method('getTopicName')->willReturn('sample_topic');
        $messageMock->expects($this->once())->method('getId')->willReturn('1');
        $fileName = 'sample_topic_1.json';

        $rawResponseMock->expects($this->once())->method('setHttpResponseCode')->with(200);
        $rawResponseMock->expects($this->exactly(5))->method('setHeader')->withConsecutive(
            ['Pragma', 'public', true],
            ['Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true],
            ['Content-type', 'application/json', true],
            ['Content-Length', $contentLength, true],
            ['Content-Disposition', 'attachment; filename="' . $fileName . '"', true]
        );
        $rawResponseMock->expects($this->once())->method('setContents')->with($messageBody);

        $result = $this->sut->map($messageMock, $rawResponseMock);

        $this->assertEquals($rawResponseMock, $result);
    }

    public function testItShouldThrowAnExceptionWhenMessageBodyIsEmpty(): void
    {
        $messageMock = $this->createMock(Message::class);
        $rawResponseMock = $this->createMock(RawResponse::class);

        $messageMock->expects($this->once())->method('getMessageBody')->willReturn('');
        $this->expectException(EmptyQueueMessageBodyException::class);

        $this->sut->map($messageMock, $rawResponseMock);
    }
}
