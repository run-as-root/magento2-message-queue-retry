<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Converter;

use Magento\Framework\Config\ConverterInterface;
use RunAsRoot\MessageQueueRetry\Config\QueueRetryConfigInterface;

class QueueRetryXmlToArrayConverter implements ConverterInterface
{
    /**
     * @return  array<string, array<string, array<string,int|string|null>>>
     */
    public function convert($source): array
    {
        $topics = [];

        foreach ($source->getElementsByTagName('topic') as $topicNode) {
            $topicAttributes = $topicNode->attributes;
            $topicName = $topicAttributes->getNamedItem('name')?->nodeValue;
            $retryLimit = (int)$topicAttributes->getNamedItem('retryLimit')?->nodeValue;

            $topics[$topicName] = [
                QueueRetryConfigInterface::TOPIC_NAME => $topicName,
                QueueRetryConfigInterface::RETRY_LIMIT => $retryLimit,
            ];
        }

        return [ QueueRetryConfigInterface::CONFIG_KEY_NAME => $topics ];
    }
}
