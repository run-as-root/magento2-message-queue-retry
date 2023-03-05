<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Message;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Ui\Component\MassAction\Filter;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\Message\CollectionFactory;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;

class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'RunAsRoot_MessageQueueRetry::mass_delete';

    public function __construct(
        Context $context,
        private MessageRepository $messageRepository,
        private RedirectFactory $redirectFactory,
        private CollectionFactory $collectionFactory,
        private Filter $filter
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $redirect = $this->redirectFactory->create();

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            foreach ($collection->getItems() as $message) {
                $this->messageRepository->delete($message);
            }

            $this->messageManager->addSuccessMessage(__('The messages have been successfully deleted'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while trying to delete the messages: %1', $e->getMessage())
            );
        }

        $redirect->setPath('message_queue_retry/index/index');

        return $redirect;
    }
}
