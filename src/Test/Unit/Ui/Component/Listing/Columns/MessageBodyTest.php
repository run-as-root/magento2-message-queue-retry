<?php declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\MessageQueueRetry\Ui\Component\Listing\Columns\Actions;
use RunAsRoot\MessageQueueRetry\Ui\Component\Listing\Columns\MessageBody;

final class MessageBodyTest extends TestCase
{
    private MessageBody $sut;
    private ContextInterface|MockObject $contextMock;
    private UiComponentFactory|MockObject $uiComponentFactoryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);

        $data = [
            'config' => [
                'label' => 'Message Body',
                'dataType' => 'text',
                'component' => 'Magento_Ui/js/grid/columns/column',
                'componentType' => 'column',
                'filter' => 'text',
            ],
            'name' => 'message_body'
        ];

        $this->sut = new MessageBody($this->contextMock, $this->uiComponentFactoryMock, [], $data);
    }

    public function testPrepareDataSource(): void
    {
        $dataSource = [
            'data' => [
                'items' => [
                    ['id_field_name' => 'entity_id', 'entity_id' => '1'],
                    [
                        'id_field_name' => 'entity_id',
                        'entity_id' => '2',
                        'message_body' => $this->getMessageBody()
                    ],
                ],
                'totalRecords' => 2,
            ],
        ];

        $result = $this->sut->prepareDataSource($dataSource);

        $expected = [
            'data' => [
                'items' => [
                    ['id_field_name' => 'entity_id', 'entity_id' => '1'],
                    [
                        'id_field_name' => 'entity_id',
                        'entity_id' => '2',
                        'message_body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the ...'
                    ],
                ],
                'totalRecords' => 2,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    private function getMessageBody(): string
    {
        return 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the best thing in the world.';
    }
}
