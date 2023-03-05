<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class MessageBody extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['message_body']) || !$item['message_body']) {
                    continue;
                }

                $item['message_body'] = substr($item['message_body'], 0, 100) . '...';
            }
        }

        return $dataSource;
    }
}
