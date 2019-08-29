<?php

namespace PermitsTest\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\CurrencyFormatter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Permits\Data\Mapper\EcmtNoOfPermits;
use Permits\Data\Mapper\FeeList;

class FeeListTest extends MockeryTestCase
{
    private $translator;

    private $currencyFormatter;

    private $ecmtNoOfPermits;

    private $feeList;

    public function setUp()
    {
        $this->translator = m::mock(TranslationHelperService::class);

        $this->currencyFormatter = m::mock(CurrencyFormatter::class);

        $this->ecmtNoOfPermits = m::mock(EcmtNoOfPermits::class);

        $this->feeList = new FeeList(
            $this->translator,
            $this->currencyFormatter,
            $this->ecmtNoOfPermits
        );
    }

    public function testMapForDisplayEcmtAnnual()
    {
        $application = [
            'applicationRef' => 'OG4563323 / 4',
            'dateReceived' => '2019-05-23 14:23:23+00:00',
            'irhpPermitApplications' => [
                [
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                            'validTo' => '2023-12-31 23:59:59+00:00'
                        ]
                    ]
                ]
            ],
            'permitType' => [
                'id' => RefData::PERMIT_TYPE_ECMT,
                'description' => 'Annual ECMT'
            ],
            'requiredEuro5' => 4,
            'requiredEuro6' => 7,
            'totalPermitsRequired' => 11
        ];

        $inputData = [
            'application' => $application,
            'irhpFeeList' => [
                'fee' => [
                    'IRHP_GV_APP_ECMT' => [
                        'fixedValue' => '10'
                    ]
                ]
            ]
        ];

        $expectedSummaryData = [
            [
                'key' => 'permits.page.fee.application.reference',
                'value' => 'OG4563323 / 4'
            ],
            [
                'key' => 'permits.page.fee.application.date',
                'value' => '23 May 2019'
            ],
            [
                'key' => 'permits.page.fee.permit.type',
                'value' => 'Annual ECMT'
            ],
            [
                'key' => 'permits.page.fee.permit.year',
                'value' => '2023'
            ],
            [
                'key' => 'permits.page.fee.number.permits',
                'value' => 'no of permits line 1<br/>no of permits line 2',
                'disableHtmlEscape' => true
            ],
            [
                'key' => 'permits.page.fee.application.fee.per.permit',
                'value' => '10',
                'isCurrency' => true
            ],
            [
                'key' => 'permits.page.fee.permit.fee.total',
                'value' => '£110 (non-refundable)'
            ]
        ];

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with('110')
            ->andReturn('£110');

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                ['£110']
            )
            ->andReturn('£110 (non-refundable)');
 
        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with($application)
            ->andReturn(['no of permits line 1', 'no of permits line 2']);

        $returnedData = $this->feeList->mapForDisplay($inputData);

        $this->assertEquals(
            $expectedSummaryData,
            $returnedData['summaryData']
        );
    }

    public function testMapForDisplayOther()
    {
        $application = [
            'applicationRef' => 'OG4563323 / 4',
            'dateReceived' => '2019-05-23 14:23:23+00:00',
            'irhpPermitApplications' => [
                [
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                            'validFrom' => '2023-01-01 00:00:00+00:00',
                            'validTo' => '2023-12-31 23:59:59+00:00'
                        ]
                    ]
                ]
            ],
            'permitType' => [
                'id' => 'permit_type_other',
                'description' => 'Other permit type'
            ],
            'permitsRequired' => 5
        ];

        $inputData = [
            'application' => $application,
            'irhpFeeList' => [
                'fee' => [
                    'IRHP_GV_APP_ECMT' => [
                        'fixedValue' => '10'
                    ],
                    'IRHP_GV_ECMT_100_PERMIT_FEE' => [
                        'fixedValue' => '15'
                    ]
                ]
            ]
        ];

        $expectedSummaryData = [
            [
                'key' => 'permits.page.fee.application.reference',
                'value' => 'OG4563323 / 4'
            ],
            [
                'key' => 'permits.page.fee.application.date',
                'value' => '23 May 2019'
            ],
            [
                'key' => 'permits.page.fee.permit.type',
                'value' => 'Other permit type'
            ],
            [
                'key' => 'permits.page.fee.permit.validity',
                'value' => '01 Jan 2023 to 31 Dec 2023'
            ],
            [
                'key' => 'permits.page.fee.number.permits.required',
                'value' => '5 x £10 (per permit)'
            ],
            [
                'key' => 'permits.page.fee.permit.fee.total',
                'value' => '£50 (non-refundable)'
            ]
        ];

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with('10')
            ->andReturn('£10');

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with('50')
            ->andReturn('£50');

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.validity.dates',
                ['01 Jan 2023', '31 Dec 2023']
            )
            ->andReturn('01 Jan 2023 to 31 Dec 2023');

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.value',
                [5, '£10']
            )
            ->andReturn('5 x £10 (per permit)');

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                ['£50']
            )
            ->andReturn('£50 (non-refundable)');
 
        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with($application)
            ->andReturn(['no of permits line 1', 'no of permits line 2']);

        $returnedData = $this->feeList->mapForDisplay($inputData);

        $this->assertEquals(
            $expectedSummaryData,
            $returnedData['summaryData']
        );
    }
}
