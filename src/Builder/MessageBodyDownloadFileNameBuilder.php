<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Builder;

use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessage;

class MessageBodyDownloadFileNameBuilder
{
    public function build(QueueErrorMessage $message): string
    {
        return $message->getTopicName() . '_' . $message->getId() . '.json';
    }
}
