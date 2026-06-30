<?php

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\IrhpPermitTypeWithValidityDate;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Irhp Permit Type With Validity Date test
 */
class IrhpPermitTypeWithValidityDateTest extends MockeryTestCase
{
    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new IrhpPermitTypeWithValidityDate(new Date(), $this->translator);
    }

    /**
     * @dataProvider scenariosProvider
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
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return array{'ECMT Annual - without validity date': list{array{typeId: 1, typeDescription: 'Annual ECMT>'}, 'Annual ECMT&gt;'}, 'ECMT Annual - with validity date': list{array{typeId: 1, typeDescription: 'Annual ECMT>', stockValidTo: '2019-12-31'}, 'Annual ECMT&gt; 2019'}, 'ECMT Short Term - without validity date': list{array{typeId: 2, typeDescription: 'Short-term ECMT>'}, 'Short-term ECMT&gt;'}, 'ECMT Short Term - with validity date 2019': list{array{typeId: 2, typeDescription: 'Short-term ECMT>', stockValidTo: '2019-12-31'}, 'Short-term ECMT&gt; 2019'}, 'ECMT Short Term - with validity date 2020': list{array{typeId: 2, typeDescription: 'Short-term ECMT>', stockValidTo: '2020-12-31', periodNameKey: 'imATranslationKey'}, 'Short-term ECMT&gt; _TRNSLT_imATranslationKey'}, 'IRHP Bilateral - without validity date': list{array{typeId: 4, typeDescription: 'Annual Bilateral>'}, 'Annual Bilateral&gt;'}, 'IRHP Bilateral - with validity date': list{array{typeId: 4, typeDescription: 'Annual Bilateral>', stockValidTo: '2019-12-31'}, 'Annual Bilateral&gt;'}, 'IRHP Multilateral - without validity date': list{array{typeId: 5, typeDescription: 'Annual Multilateral>'}, 'Annual Multilateral&gt;'}, 'IRHP Multilateral - with validity date': list{array{typeId: 5, typeDescription: 'Annual Multilateral>', stockValidTo: '2019-12-31'}, 'Annual Multilateral&gt;'}, 'ECMT International Removal - without validity date': list{array{typeId: 3, typeDescription: 'ECMT International Removal>'}, 'ECMT International Removal&gt;'}, 'ECMT International Removal - with validity date': list{array{typeId: 3, typeDescription: 'ECMT International Removal>', stockValidTo: '2019-12-31'}, 'ECMT International Removal&gt;'}}
     */
    public function scenariosProvider(): array
    {
        return [
            'ECMT Annual - without validity date' => [
                [
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'typeDescription' => 'Annual ECMT>',
                ],
                'Annual ECMT&gt;',
            ],
            'ECMT Annual - with validity date' => [
                [
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'typeDescription' => 'Annual ECMT>',
                    'stockValidTo' => '2019-12-31',
                ],
                'Annual ECMT&gt; 2019',
            ],
            'ECMT Short Term - without validity date' => [
                [
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'typeDescription' => 'Short-term ECMT>',
                ],
                'Short-term ECMT&gt;',
            ],
            'ECMT Short Term - with validity date 2019' => [
                [
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'typeDescription' => 'Short-term ECMT>',
                    'stockValidTo' => '2019-12-31',
                ],
                'Short-term ECMT&gt; 2019',
            ],
            'ECMT Short Term - with validity date 2020' => [
                [
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'typeDescription' => 'Short-term ECMT>',
                    'stockValidTo' => '2020-12-31',
                    'periodNameKey' => 'imATranslationKey'
                ],
                'Short-term ECMT&gt; _TRNSLT_imATranslationKey',
            ],
            'IRHP Bilateral - without validity date' => [
                [
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'typeDescription' => 'Annual Bilateral>',
                ],
                'Annual Bilateral&gt;',
            ],
            'IRHP Bilateral - with validity date' => [
                [
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'typeDescription' => 'Annual Bilateral>',
                    'stockValidTo' => '2019-12-31',
                ],
                'Annual Bilateral&gt;',
            ],
            'IRHP Multilateral - without validity date' => [
                [
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'typeDescription' => 'Annual Multilateral>',
                ],
                'Annual Multilateral&gt;',
            ],
            'IRHP Multilateral - with validity date' => [
                [
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'typeDescription' => 'Annual Multilateral>',
                    'stockValidTo' => '2019-12-31',
                ],
                'Annual Multilateral&gt;',
            ],
            'ECMT International Removal - without validity date' => [
                [
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'typeDescription' => 'ECMT International Removal>',
                ],
                'ECMT International Removal&gt;',
            ],
            'ECMT International Removal - with validity date' => [
                [
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'typeDescription' => 'ECMT International Removal>',
                    'stockValidTo' => '2019-12-31',
                ],
                'ECMT International Removal&gt;',
            ],
        ];
    }
}
