<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Message;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Ui\Component\MassAction\Filter;
use RunAsRoot\MessageQueueRetry\Model\QueueErrorMessage;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\QueueErrorMessage\QueueErrorMessageCollectionFactory;
use RunAsRoot\MessageQueueRetry\Service\PublishMessageToQueueService;

class MassRequeue extends Action
{
    public const ADMIN_RESOURCE = 'RunAsRoot_MessageQueueRetry::mass_requeue';

    public function __construct(
        Context $context,
        private PublishMessageToQueueService $publishMessageToQueueService,
        private RedirectFactory $redirectFactory,
        private QueueErrorMessageCollectionFactory $collectionFactory,
        private Filter $filter
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $redirect = $this->redirectFactory->create();

        try {
            /** @var AbstractDb $messageCollection */
            $messageCollection = $this->collectionFactory->create();
            $collection = $this->filter->getCollection($messageCollection);

            foreach ($collection->getItems() as $message) {
                if (!$message instanceof QueueErrorMessage) {
                    continue;
                }

                $this->publishMessageToQueueService->executeByMessage($message);
            }

            $this->messageManager->addSuccessMessage(__('Messages queued successfully')->render());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while trying to requeue the message: %1', $e->getMessage())->render()
            );
        }

        $redirect->setPath('message_queue_retry/index/index');

        return $redirect;
    }
}
