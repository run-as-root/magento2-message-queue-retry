<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Service;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use PhpAmqpLib\Wire\AMQPTable;

class GetMessageRetriesCountService
{
    public function execute(EnvelopeInterface $message): int
    {
        $messageProperties = $message->getProperties();
        $applicationHeaders = $messageProperties['application_headers'] ?? null;

        // If there are no application headers, then it is the first time the message has been processed.
        if (!$applicationHeaders instanceof AMQPTable) {
            return 0;
        }

        if (isset($applicationHeaders->getNativeData()['x-death'][0]['count'])) {
            return $applicationHeaders->getNativeData()['x-death'][0]['count'];
        }

        return 0;
    }
}
