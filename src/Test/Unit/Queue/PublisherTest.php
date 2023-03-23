<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Queue;

use Magento\Framework\MessageQueue\Envelope;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Exception\InvalidMessageQueueConnectionTypeException;
use RunAsRoot\MessageQueueRetry\Exception\InvalidPublisherConfigurationException;
use RunAsRoot\MessageQueueRetry\Queue\Publisher;

final class PublisherTest extends TestCase
{
    private Publisher $sut;
    private ExchangeRepository|MockObject $exchangeRepositoryMock;
    private EnvelopeFactory|MockObject $envelopeFactoryMock;
    private PublisherConfig|MockObject $publisherConfigMock;

    protected function setUp(): void
    {
        $this->exchangeRepositoryMock = $this->createMock(ExchangeRepository::class);
        $this->envelopeFactoryMock = $this->createMock(EnvelopeFactory::class);
        $this->publisherConfigMock = $this->createMock(PublisherConfig::class);
        $this->sut = new Publisher(
            $this->exchangeRepositoryMock,
            $this->envelopeFactoryMock,
            $this->publisherConfigMock
        );
    }

    public function testPublish(): void
    {
        $topicName = 'topic.name';
        $data = '{"foo": "bar"}';

        $envelopeMock = $this->createMock(Envelope::class);
        $this->envelopeFactoryMock->expects($this->once())->method('create')->willReturn($envelopeMock);

        $publisherConfigItemMock = $this->createMock(PublisherConfigItemInterface::class);
        $this->publisherConfigMock->expects($this->once())->method('getPublisher')->willReturn($publisherConfigItemMock);

        $publisherConnectionMock = $this->createMock(PublisherConnectionInterface::class);
        $publisherConfigItemMock->expects($this->once())->method('getConnection')->willReturn($publisherConnectionMock);
        $publisherConnectionMock->expects($this->once())->method('getName')->willReturn('amqp');

        $exchangeMock = $this->createMock(ExchangeInterface::class);
        $this->exchangeRepositoryMock->expects($this->once())->method('getByConnectionName')->willReturn($exchangeMock);
        $exchangeMock->expects($this->once())->method('enqueue')->with($topicName, $envelopeMock);

        $this->sut->publish($topicName, $data);
    }

    public function testPublishWithInvalidPublisherConfiguration(): void
    {
        $topicName = 'topic.name';
        $data = '{"foo": "bar"}';

        $this->expectException(InvalidPublisherConfigurationException::class);

        $this->publisherConfigMock->expects($this->once())->method('getPublisher')
            ->willThrowException(new \Exception('Invalid publisher configuration.'));

        $this->sut->publish($topicName, $data);
    }

    public function testPublishWithInvalidMessageQueueConnectionType(): void
    {
        $topicName = 'topic.name';
        $data = '{"foo": "bar"}';

        $this->expectException(InvalidMessageQueueConnectionTypeException::class);

        $publisherConfigItemMock = $this->createMock(PublisherConfigItemInterface::class);
        $this->publisherConfigMock->expects($this->once())->method('getPublisher')->willReturn($publisherConfigItemMock);

        $publisherConnectionMock = $this->createMock(PublisherConnectionInterface::class);
        $publisherConfigItemMock->expects($this->once())->method('getConnection')->willReturn($publisherConnectionMock);
        $publisherConnectionMock->expects($this->once())->method('getName')->willReturn('db');

        $this->sut->publish($topicName, $data);
    }
}
