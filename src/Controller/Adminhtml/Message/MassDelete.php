<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Message;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Ui\Component\MassAction\Filter;
use RunAsRoot\MessageQueueRetry\Model\Message;
use RunAsRoot\MessageQueueRetry\Model\ResourceModel\Message\CollectionFactory;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;

class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'RunAsRoot_MessageQueueRetry::mass_delete';

    public function __construct(
        Context $context,
        private MessageRepository $messageRepository,
        private RedirectFactory $redirectFactory,
        private CollectionFactory $collectionFactory, // @phpstan-ignore-line
        private Filter $filter
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $redirect = $this->redirectFactory->create();

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create()); // @phpstan-ignore-line

            foreach ($collection->getItems() as $message) {
                if (!$message instanceof Message) {
                    continue;
                }
                $this->messageRepository->delete($message);
            }

            $this->messageManager->addSuccessMessage(__('The messages have been successfully deleted')->render());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while trying to delete the messages: %1', $e->getMessage())->render()
            );
        }

        $redirect->setPath('message_queue_retry/index/index');

        return $redirect;
    }
}
