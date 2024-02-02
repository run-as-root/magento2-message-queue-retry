<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Message;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use RunAsRoot\MessageQueueRetry\Service\PublishMessageToQueueService;

class Requeue extends Action
{
    public const ADMIN_RESOURCE = 'RunAsRoot_MessageQueueRetry::requeue';

    public function __construct(
        Context $context,
        private readonly PublishMessageToQueueService $publishMessageToQueueService,
        private readonly RedirectFactory $redirectFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $messageId = (int)$this->getRequest()->getParam('message_id');
        $redirect = $this->redirectFactory->create();
        $redirect->setPath('message_queue_retry/index/index');

        if (!$messageId) {
            $this->messageManager->addErrorMessage(__('Invalid message id provided in the request params')->render());
            return $redirect;
        }

        try {
            $this->publishMessageToQueueService->executeById($messageId);
            $this->messageManager->addSuccessMessage(__('Message queued successfully')->render());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while trying to requeue the message: %1', $e->getMessage())->render()
            );
        }

        return $redirect;
    }
}
