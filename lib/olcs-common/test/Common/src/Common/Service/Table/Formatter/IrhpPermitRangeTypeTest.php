<?php

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class IrhpPermitRangeTypeTest extends MockeryTestCase
{
    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new IrhpPermitRangeType($this->translator);
    }

    /**
     * @dataProvider dpFormat
     */
    public function testFormat($row, $expectedOutput): void
    {
        $column = ['name' => 'typeDescription'];

        $this->translator->shouldReceive('translate')
            ->andReturnUsing(
                static fn($key) => '_TRNSLT_' . $key
            );

        $this->assertEquals(
            $expectedOutput,
            $this->sut->format($row, $column)
        );
    }

    /**
     * @return ((((bool|string)[]|string)[]|bool)[]|string)[][]
     *
     * @psalm-return list{list{array{irhpPermitStock: array{irhpPermitType: array{isBilateral: false}}}, 'N/A'}, list{array{irhpPermitStock: array{irhpPermitType: array{isBilateral: true}}, cabotage: false, journey: array{id: 'journey_single'}}, '_TRNSLT_permits.irhp.range.type.standard.single'}, list{array{irhpPermitStock: array{irhpPermitType: array{isBilateral: true}}, cabotage: false, journey: array{id: 'journey_multiple'}}, '_TRNSLT_permits.irhp.range.type.standard.multiple'}, list{array{irhpPermitStock: array{irhpPermitType: array{isBilateral: true}}, cabotage: true, journey: array{id: 'journey_single'}}, '_TRNSLT_permits.irhp.range.type.cabotage.single'}, list{array{irhpPermitStock: array{irhpPermitType: array{isBilateral: true}}, cabotage: true, journey: array{id: 'journey_multiple'}}, '_TRNSLT_permits.irhp.range.type.cabotage.multiple'}, list{array{irhpPermitStock: array{irhpPermitType: array{isBilateral: true}, permitCategory: array{description: 'category'}}}, '_TRNSLT_category'}}
     */
    public function dpFormat(): array
    {
        return [
            [
                [
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'isBilateral' => false,
                        ]
                    ]
                ],
                'N/A',
            ],
            [
                [
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'isBilateral' => true,
                        ]
                    ],
                    'cabotage' => false,
                    'journey' => ['id' => RefData::JOURNEY_SINGLE],
                ],
                '_TRNSLT_permits.irhp.range.type.standard.single',
            ],
            [
                [
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'isBilateral' => true,
                        ]
                    ],
                    'cabotage' => false,
                    'journey' => ['id' => RefData::JOURNEY_MULTIPLE],
                ],
                '_TRNSLT_permits.irhp.range.type.standard.multiple',
            ],
            [
                [
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'isBilateral' => true,
                        ]
                    ],
                    'cabotage' => true,
                    'journey' => ['id' => RefData::JOURNEY_SINGLE],
                ],
                '_TRNSLT_permits.irhp.range.type.cabotage.single',
            ],
            [
                [
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'isBilateral' => true,
                        ]
                    ],
                    'cabotage' => true,
                    'journey' => ['id' => RefData::JOURNEY_MULTIPLE],
                ],
                '_TRNSLT_permits.irhp.range.type.cabotage.multiple',
            ],
            [
                [
                    'irhpPermitStock' => [
                        'irhpPermitType' => [
                            'isBilateral' => true,
                        ],
                        'permitCategory' => [
                            'description' => 'category',
                        ],
                    ],
                ],
                '_TRNSLT_category',
            ],
        ];
    }
}
