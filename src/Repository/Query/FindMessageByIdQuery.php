<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Repository\Query;

use RunAsRoot\MessageQueueRetry\Exception\MessageNotFoundException;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Model\MessageFactory as ModelFactory;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\MessageResource as ResourceModel;

class FindMessageByIdQuery
{
    public function __construct(
        private ResourceModel $resourceModel,
        private ModelFactory $modelFactory
    ) {
    }

    /**
     * @throws MessageNotFoundException
     */
    public function execute(int $entityId): Message
    {
        $model = $this->modelFactory->create();
        $this->resourceModel->load($model, $entityId);

        if (!$model->getId()) {
            throw new MessageNotFoundException(__('Message with id "%1" could not be found.', $entityId));
        }

        return $model;
    }
}
