<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Controller\Adminhtml\Message;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw as RawResponse;
use Magento\Framework\Controller\Result\RawFactory;
use RunAsRoot\MessageQueueRetry\Exception\EmptyQueueMessageBodyException;
use RunAsRoot\MessageQueueRetry\Exception\MessageNotFoundException;
use RunAsRoot\MessageQueueRetry\Mapper\MessageToRawResponseMapper;
use RunAsRoot\MessageQueueRetry\Repository\MessageRepository;

class Download extends Action
{
    public const ADMIN_RESOURCE = 'RunAsRoot_MessageQueueRetry::download';

    public function __construct(
        Context $context,
        private MessageRepository $messageRepository,
        private RawFactory $rawFactory,
        private MessageToRawResponseMapper $messageToRawResponseMapper
    ) {
        parent::__construct($context);
    }

    /**
     * @throws EmptyQueueMessageBodyException
     * @throws MessageNotFoundException
     */
    public function execute(): RawResponse
    {
        $messageId = (int)$this->getRequest()->getParam('message_id');
        $message = $this->messageRepository->findById($messageId);
        $rawResponse = $this->rawFactory->create();
        $this->messageToRawResponseMapper->map($message, $rawResponse);

        return $rawResponse;
    }
}
