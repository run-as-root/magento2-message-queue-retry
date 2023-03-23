<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['requeue'] = [
                'href' => $this->context->getUrl(
                    'message_queue_retry/message/requeue',
                    ['message_id' => $item['entity_id']]
                ),
                'label' => __('Requeue it'),
                'hidden' => false,
            ];
            $item[$this->getData('name')]['download'] = [
                'href' => $this->context->getUrl(
                    'message_queue_retry/message/download',
                    ['message_id' => $item['entity_id']]
                ),
                'label' => __('Download'),
                'hidden' => false,
            ];
        }

        return $dataSource;
    }
}
