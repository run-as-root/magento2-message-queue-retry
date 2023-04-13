<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\SchemaLocator;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\SchemaLocatorInterface;
use RunAsRoot\MessageQueueRetry\Config\QueueRetryConfigInterface;

class QueueRetrySchemaLocator implements SchemaLocatorInterface
{
    public function __construct(private UrnResolver $urnResolver)
    {
    }

    public function getSchema(): ?string
    {
        return $this->urnResolver->getRealPath(QueueRetryConfigInterface::XSD_FILE_URN);
    }

    public function getPerFileSchema(): ?string
    {
        return $this->urnResolver->getRealPath(QueueRetryConfigInterface::XSD_FILE_URN);
    }
}
