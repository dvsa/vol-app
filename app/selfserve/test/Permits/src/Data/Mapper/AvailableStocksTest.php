<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
use Permits\Data\Mapper\AvailableStocks;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use RuntimeException;

/**
 * AvailableStocksTest
 */
class AvailableStocksTest extends TestCase
{
    private $sut;

    public function setUp(): void
    {
        $this->sut = new AvailableStocks();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestExceptionNotSupported')]
    public function testExceptionNotSupported(int $typeId): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This mapper does not support permit type ' . $typeId);

        $data = [
            'type' => $typeId
        ];

        $this->sut->mapForFormOptions(
            $data,
            m::mock(Form::class)
        );
    }

    /**
     * @return int[][]
     *
     * @psalm-return list{list{1}, list{3}, list{4}, list{5}}
     */
    public static function dpTestExceptionNotSupported(): array
    {
        return [
            [RefData::ECMT_PERMIT_TYPE_ID],
            [RefData::ECMT_REMOVAL_PERMIT_TYPE_ID],
            [RefData::IRHP_BILATERAL_PERMIT_TYPE_ID],
            [RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID],
        ];
    }

    public function testEcmtShortTermSingleOption(): void
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'stocks' => [
                'stocks' => [
                    [
                        'id' => 1,
                        'periodNameKey' => 'period.name.key.1',
                    ],
                ],
                'selectedStock' => 1
            ],
        ];

        $expectedValueOptions = [
            [
                'value' => 1,
                'label' => 'period.name.key.1',
                'attributes' => [
                    'id' => 'stock'
                ],
                'selected' => true
            ]
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('stock')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        $expectedData = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'stocks' => [
                'stocks' => [
                    [
                        'id' => 1,
                        'periodNameKey' => 'period.name.key.1',
                    ],
                ],
                'selectedStock' => 1
            ],
            'guidance' => [
                'value' => 'permits.page.stock.guidance.one-available',
                'disableHtmlEscape' => true,
            ],
        ];

        $returnedData = $this->sut->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }

    public function testEcmtShortTermMultipleOptions(): void
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'stocks' => [
                'stocks' => [
                    [
                        'id' => 1,
                        'periodNameKey' => 'period.name.key.1',
                    ],
                    [
                        'id' => 2,
                        'periodNameKey' => 'period.name.key.2',
                    ],
                ],
                'selectedStock' => 2
            ],
        ];

        $expectedValueOptions = [
            [
                'value' => 1,
                'label' => 'period.name.key.1',
                'attributes' => [
                    'id' => 'stock'
                ],
                'selected' => false
            ],
            [
                'value' => 2,
                'label' => 'period.name.key.2',
                'selected' => true
            ],
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('stock')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        $expectedData = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'stocks' => [
                'stocks' => [
                    [
                        'id' => 1,
                        'periodNameKey' => 'period.name.key.1',
                    ],
                    [
                        'id' => 2,
                        'periodNameKey' => 'period.name.key.2',
                    ],
                ],
                'selectedStock' => 2
            ],
            'guidance' => [
                'value' => 'permits.page.stock.guidance.multiple-available',
                'disableHtmlEscape' => true,
            ],
        ];

        $returnedData = $this->sut->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }
}
