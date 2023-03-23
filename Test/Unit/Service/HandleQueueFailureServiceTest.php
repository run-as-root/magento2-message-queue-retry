<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Service;

use Magento\Framework\Amqp\Queue;
use Magento\Framework\MessageQueue\Envelope;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Model\MessageFactory;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;
use RunAsRoot\MessageQueueRetry\Service\HandleQueueFailureService;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig;

class HandleQueueFailureServiceTest extends TestCase
{
    private HandleQueueFailureService $sut;
    private MessageQueueRetryConfig|MockObject $messageQueueRetryConfigMock;
    private MessageFactory|MockObject $messageFactoryMock;
    private MessageRepository|MockObject $messageRepositoryMock;

    protected function setUp(): void
    {
        $this->messageQueueRetryConfigMock = $this->createMock(MessageQueueRetryConfig::class);
        $this->messageFactoryMock = $this->createMock(MessageFactory::class);
        $this->messageRepositoryMock = $this->createMock(MessageRepository::class);
        $this->sut = new HandleQueueFailureService(
            $this->messageQueueRetryConfigMock,
            $this->messageFactoryMock,
            $this->messageRepositoryMock
        );
    }

    public function testExecute(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $messageBody = '{"data": "Oh Yeah!"}';
        $messageMock->expects($this->once())->method('getBody')->willReturn($messageBody);
        $queueMock = $this->createMock(Queue::class);

        $exceptionMessage = 'exception message';
        $exception = new \Exception($exceptionMessage);

        $this->messageQueueRetryConfigMock->expects($this->once())->method('isDelayQueueEnabled')->willReturn(true);

        $applicationHeaders = new AMQPTable(['x-death' => [['count' => 1]]]);
        $topicName = 'sample_topic';
        $messageProperties = ['application_headers' => $applicationHeaders, 'topic_name' => $topicName];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $queueConfig = [$topicName => ['retry_limit' => 1]];
        $this->messageQueueRetryConfigMock->expects($this->once())->method('getDelayQueues')->willReturn($queueConfig);

        $messageModelMock = $this->createMock(Message::class);
        $this->messageFactoryMock->expects($this->once())->method('create')->willReturn($messageModelMock);

        $messageModelMock->expects($this->once())->method('setTopicName')->with($topicName);
        $messageModelMock->expects($this->once())->method('setMessageBody')->with($messageBody);
        $messageModelMock->expects($this->once())->method('setFailureDescription')->with($exceptionMessage);
        $messageModelMock->expects($this->once())->method('setTotalRetries')->with(2);

        $this->messageRepositoryMock->expects($this->once())->method('create')->with($messageModelMock);

        $queueMock->expects($this->once())->method('acknowledge')->with($messageMock);
        $queueMock->expects($this->never())->method('reject');

        $this->sut->execute($queueMock, $messageMock, $exception);
    }

    public function testItShouldRejectTheMessageWhenTheRetryLimitIsNotReached(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $queueMock = $this->createMock(Queue::class);

        $exceptionMessage = 'exception message';
        $exception = new \Exception($exceptionMessage);

        $this->messageQueueRetryConfigMock->expects($this->once())->method('isDelayQueueEnabled')->willReturn(true);

        $applicationHeadersMock = $this->createMock(AMQPTable::class);
        $topicName = 'sample_topic';
        $messageProperties = ['application_headers' => $applicationHeadersMock, 'topic_name' => $topicName];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $queueConfig = [$topicName => ['retry_limit' => 3]];
        $this->messageQueueRetryConfigMock->expects($this->once())->method('getDelayQueues')->willReturn($queueConfig);

        $this->messageFactoryMock->expects($this->never())->method('create');
        $this->messageRepositoryMock->expects($this->never())->method('create');

        $queueMock->expects($this->never())->method('acknowledge');
        $queueMock->expects($this->once())->method('reject')->with($messageMock, false, $exceptionMessage);

        $this->sut->execute($queueMock, $messageMock, $exception);
    }

    public function testItShouldRejectTheMessageWhenDelayQueueIsNotEnabled(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $queueMock = $this->createMock(Queue::class);
        $exception = new \Exception('test');

        $this->messageQueueRetryConfigMock->expects($this->once())->method('isDelayQueueEnabled')->willReturn(false);

        $queueMock->expects($this->once())->method('reject')->with($messageMock, false, 'test');

        $messageMock->expects($this->never())->method('getProperties');

        $this->sut->execute($queueMock, $messageMock, $exception);
    }

    public function testItShouldRejectTheMessageWhenThereAreNoMessageProperties(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $queueMock = $this->createMock(Queue::class);
        $exception = new \Exception('test');

        $this->messageQueueRetryConfigMock->expects($this->once())->method('isDelayQueueEnabled')->willReturn(true);

        $messageMock->expects($this->once())->method('getProperties')->willReturn(null);

        $queueMock->expects($this->once())->method('reject')->with($messageMock, false, 'test');

        $this->sut->execute($queueMock, $messageMock, $exception);
    }

    public function testItShouldRejectTheMessageWhenThereAreNoApplicationHeaders(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $queueMock = $this->createMock(Queue::class);
        $exception = new \Exception('test');

        $this->messageQueueRetryConfigMock->expects($this->once())->method('isDelayQueueEnabled')->willReturn(true);

        $messageMock->expects($this->once())->method('getProperties')->willReturn(['application_headers' => null]);

        $queueMock->expects($this->once())->method('reject')->with($messageMock, false, 'test');

        $this->sut->execute($queueMock, $messageMock, $exception);
    }

    public function testItShouldRejectTheMessageWhenThereIsNoTopicNameInTheMessageProperties(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $queueMock = $this->createMock(Queue::class);
        $exception = new \Exception('test');

        $this->messageQueueRetryConfigMock->expects($this->once())->method('isDelayQueueEnabled')->willReturn(true);

        $applicationHeadersMock = $this->createMock(AMQPTable::class);
        $messageProperties = ['application_headers' => $applicationHeadersMock];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $queueMock->expects($this->once())->method('reject')->with($messageMock, false, 'test');

        $this->sut->execute($queueMock, $messageMock, $exception);
    }

    public function testItShouldRejectTheMessageWhenThereIsNoQueueConfiguration(): void
    {
        $messageMock = $this->createMock(Envelope::class);
        $queueMock = $this->createMock(Queue::class);
        $exception = new \Exception('test');

        $this->messageQueueRetryConfigMock->expects($this->once())->method('isDelayQueueEnabled')->willReturn(true);

        $applicationHeadersMock = $this->createMock(AMQPTable::class);
        $messageProperties = ['application_headers' => $applicationHeadersMock, 'topic_name' => 'sample_topic'];
        $messageMock->expects($this->once())->method('getProperties')->willReturn($messageProperties);

        $this->messageQueueRetryConfigMock->expects($this->once())->method('getDelayQueues')->willReturn([]);

        $queueMock->expects($this->once())->method('reject')->with($messageMock, false, 'test');

        $this->sut->execute($queueMock, $messageMock, $exception);
    }
}
