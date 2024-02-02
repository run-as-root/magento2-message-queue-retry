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
use RunAsRoot\MessageQueueRetry\Repository\QueueErrorMessageRepository;

class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'RunAsRoot_MessageQueueRetry::mass_delete';

    public function __construct(
        Context $context,
        private readonly QueueErrorMessageRepository $messageRepository,
        private readonly RedirectFactory $redirectFactory,
        private readonly QueueErrorMessageCollectionFactory $collectionFactory,
        private readonly Filter $filter
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
