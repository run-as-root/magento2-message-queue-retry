<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\SchemaLocator;

use Magento\Framework\Config\Dom\UrnResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Config\QueueRetryConfigInterface;
use RunAsRoot\MessageQueueRetry\SchemaLocator\QueueRetrySchemaLocator;

class QueueRetrySchemaLocatorTest extends TestCase
{
    private QueueRetrySchemaLocator $sut;
    private UrnResolver|MockObject $urnResolverMock;

    protected function setUp(): void
    {
        $this->urnResolverMock = $this->createMock(UrnResolver::class);
        $this->sut = new QueueRetrySchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema(): void
    {
        $urn = QueueRetryConfigInterface::XSD_FILE_URN;
        $realPath = 'some-path';
        $this->urnResolverMock->expects($this->once())->method('getRealPath')->with($urn)->willReturn($realPath);

        $result = $this->sut->getSchema();

        $this->assertEquals($realPath, $result);
    }

    public function testGetPerFileSchema(): void
    {
        $urn = QueueRetryConfigInterface::XSD_FILE_URN;
        $realPath = 'some-path';
        $this->urnResolverMock->expects($this->once())->method('getRealPath')->with($urn)->willReturn($realPath);

        $result = $this->sut->getPerFileSchema();

        $this->assertEquals($realPath, $result);
    }
}
