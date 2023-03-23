<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Builder;

use RunAsRoot\MessageQueueRetry\Model\Message;

class MessageBodyDownloadFileNameBuilder
{
    public function build(Message $message): string
    {
        return $message->getTopicName() . '_' . $message->getId() . '.json';
    }
}
