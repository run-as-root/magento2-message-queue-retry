<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Ui\Component\Listing\Columns\Actions;

final class ActionsTest extends TestCase
{
    private Actions $sut;
    private ContextInterface|MockObject $contextMock;
    private UiComponentFactory|MockObject $uiComponentFactoryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);

        $data = [
            'config' => [
                'label' => 'Action',
                'dataType' => 'actions',
                'component' => 'Magento_Ui/js/grid/columns/actions',
                'indexField' => 'identity',
                'sortOrder' => '20',
            ],
            'name' => 'actions',
            'sortOrder' => '20',
        ];

        $this->sut = new Actions($this->contextMock, $this->uiComponentFactoryMock, [], $data);
    }

    public function testPrepareDataSource(): void
    {
        $dataSource = [
            'data' => [
                'items' => [
                    ['id_field_name' => 'entity_id', 'entity_id' => '1'],
                    ['id_field_name' => 'entity_id', 'entity_id' => '2'],
                ],
                'totalRecords' => 2,
            ],
        ];

        $requeuePath = 'message_queue_retry/message/requeue';
        $downloadPath = 'message_queue_retry/message/download';

        $backofficeUrl = 'http://example.com/backofice/';
        $requeueUrlOne = $backofficeUrl . $requeuePath  . '/1';
        $requeueUrlTwo = $backofficeUrl . $requeuePath  . '/2';
        $downloadUrlOne = $backofficeUrl . $downloadPath  . '/1';
        $downloadUrlTwo = $backofficeUrl . $downloadPath  . '/2';

        $this->contextMock->expects($this->exactly(4))->method('getUrl')->withConsecutive(
            [$requeuePath, ['message_id' => '1']],
            [$downloadPath, ['message_id' => '1']],
            [$requeuePath, ['message_id' => '2']],
            [$downloadPath, ['message_id' => '2']]
        )->willReturnOnConsecutiveCalls($requeueUrlOne, $downloadUrlOne, $requeueUrlTwo, $downloadUrlTwo);

        $result = $this->sut->prepareDataSource($dataSource);

        $requeueLabel = __('Requeue it');
        $downloadLabel = __('Download');

        $expected = [
            'data' => [
                'items' => [
                    [
                        'id_field_name' => 'entity_id',
                        'entity_id' => '1',
                        'actions' => [
                            'requeue' => [
                                'href' => $requeueUrlOne,
                                'label' => $requeueLabel,
                                'hidden' => false,
                            ],
                            'download' => [
                                'href' => $downloadUrlOne,
                                'label' => $downloadLabel,
                                'hidden' => false,
                            ],
                        ],
                    ],
                    [
                        'id_field_name' => 'entity_id',
                        'entity_id' => '2',
                        'actions' => [
                            'requeue' => [
                                'href' => $requeueUrlTwo,
                                'label' => $requeueLabel,
                                'hidden' => false,
                            ],
                            'download' => [
                                'href' => $downloadUrlTwo,
                                'label' => $downloadLabel,
                                'hidden' => false,
                            ],
                        ],
                    ],
                ],
                'totalRecords' => 2,
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
