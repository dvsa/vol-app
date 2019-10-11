<?php

namespace PermitsTest\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\View\Helper\CurrencyFormatter;
use Permits\Data\Mapper\AcceptOrDeclinePermits;
use Permits\Data\Mapper\ApplicationFees;
use Permits\Data\Mapper\EcmtNoOfPermits;
use Permits\View\Helper\EcmtSection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * AcceptOrDeclinePermitsTest
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.com>
 */
class AcceptOrDeclinePermitsTest extends MockeryTestCase
{
    private $translator;

    private $urlHelperService;

    private $applicationFees;

    private $currencyFormatter;

    private $ecmtNoOfPermits;

    private $acceptOrDeclinePermits;

    public function setUp()
    {
        $this->translator = m::mock(TranslationHelperService::class);

        $this->translator->shouldReceive('translate')
            ->with('permits.page.view.permit.restrictions')
            ->andReturn('View permit restrictions');

        $this->urlHelperService = m::mock(UrlHelperService::class);

        $this->applicationFees = m::mock(ApplicationFees::class);

        $this->currencyFormatter = m::mock(CurrencyFormatter::class);

        $this->ecmtNoOfPermits = m::mock(EcmtNoOfPermits::class);

        $this->acceptOrDeclinePermits = new AcceptOrDeclinePermits(
            $this->translator,
            $this->urlHelperService,
            $this->applicationFees,
            $this->currencyFormatter,
            $this->ecmtNoOfPermits
        );
    }

    /**
     * @dataProvider dpTestMapForDisplay
     */
    public function testMapForDisplay(
        $permitsAwarded,
        $euro5PermitsAwarded,
        $euro6PermitsAwarded,
        $guidanceKey,
        $translatedGuidance
    ) {
        $applicationReference = 'OB1234567 / 3003';
        $permitTypeDescription = 'Annual ECMT';
        $feePerPermit = '62.00';
        $grossAmount = '310.00';
        $formattedGrossAmount = '£310';
        $formattedGrossAmountTranslated = '£310 (non-refundable)';
        $permitsRequired = 9;

        $data = [
            'applicationRef' => $applicationReference,
            'permitType' => [
                'description' => $permitTypeDescription
            ],
            'hasOutstandingFees' => true,
            'irhpPermitApplications' => [
                [
                    'permitsAwarded' => $permitsAwarded,
                    'euro5PermitsAwarded' => $euro5PermitsAwarded,
                    'euro6PermitsAwarded' => $euro6PermitsAwarded,
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                            'validityYear' => '2023'
                        ]
                    ]
                ]
            ],
            'fees' => [
                [
                    'isOutstanding' => true,
                    'isEcmtIssuingFee' => true,
                    'feeType' => [
                        'displayValue' => $feePerPermit,
                    ],
                    'grossAmount' => $grossAmount,
                    'invoicedDate' => '2019-07-10T00:00:00+0000'
                ]
            ],
            'totalPermitsRequired' => $permitsRequired,
        ];

        $mappedApplicationFees = [
            'issueFee' => $feePerPermit,
            'totalFee' => $grossAmount,
            'dueDate' => '2019-07-23',
        ];

        $dataWithMappedApplicationFees = array_merge(
            $data,
            $mappedApplicationFees
        );

        $this->applicationFees->shouldReceive('mapForDisplay')
            ->with($data)
            ->andReturn($dataWithMappedApplicationFees);

        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with(['requiredEuro5' => $euro5PermitsAwarded, 'requiredEuro6' => $euro6PermitsAwarded])
            ->andReturn(['ecmt no of permits line 1', 'ecmt no of permits line 2']);

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.ecmt.fee-part-successful.fee.total.value',
                [$formattedGrossAmount]
            )
            ->andReturn($formattedGrossAmountTranslated);

        $this->translator->shouldReceive('translateReplace')
            ->with(
                $guidanceKey,
                [$permitsAwarded, $permitsRequired]
            )
            ->andReturn($translatedGuidance);

        $this->urlHelperService->shouldReceive('fromRoute')
            ->with(EcmtSection::ROUTE_ECMT_UNPAID_PERMITS, [], [], true)
            ->andReturn('/permits/3003/ecmt-unpaid-permits/');

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($grossAmount)
            ->andReturn($formattedGrossAmount);

        $expectedSummaryData = [
            [
                'key' => 'permits.page.fee.application.reference',
                'value' => $applicationReference,
            ],
            [
                'key' => 'permits.page.ecmt.consideration.permit.type',
                'value' => $permitTypeDescription,
            ],
            [
                'key' => 'permits.page.ecmt.consideration.permit.year',
                'value' => '2023',
            ],
            [
                'key' => 'permits.page.fee.number.permits',
                'value' => 'ecmt no of permits line 1<br>' .
                    'ecmt no of permits line 2<br>' .
                    '<a href="/permits/3003/ecmt-unpaid-permits/">View permit restrictions</a>',
                'disableHtmlEscape' => true,
            ],
            [
                'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee',
                'value' => '62.00',
                'isCurrency' => true,
            ],
            [
                'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee.total',
                'value' => $formattedGrossAmountTranslated,
            ],
            [
                'key' => 'permits.page.ecmt.fee-part-successful.payment.due',
                'value' => '23 Jul 2019',
            ]
        ];

        $expectedGuidanceData = [
            'value' => $translatedGuidance,
            'disableHtmlEscape' => true,
        ];

        $mappedData = $this->acceptOrDeclinePermits->mapForDisplay($data);

        $this->assertEquals(
            'permits.page.fee-part-successful.title',
            $mappedData['title']
        );

        $this->assertEquals(
            $expectedSummaryData,
            $mappedData['summaryData']
        );

        $this->assertEquals(
            $expectedGuidanceData,
            $mappedData['guidance']
        );
    }

    /**
     * @dataProvider dpTestMapForDisplay
     */
    public function testMapForDisplayNoOutstandingFee(
        $permitsAwarded,
        $euro5PermitsAwarded,
        $euro6PermitsAwarded,
        $guidanceKey,
        $translatedGuidance
    ) {
        $applicationReference = 'OB1234567 / 3003';
        $permitTypeDescription = 'Annual ECMT';
        $feePerPermit = '62.00';
        $permitsRequired = 9;

        $data = [
            'applicationRef' => $applicationReference,
            'permitType' => [
                'description' => $permitTypeDescription
            ],
            'hasOutstandingFees' => false,
            'irhpPermitApplications' => [
                [
                    'permitsAwarded' => $permitsAwarded,
                    'euro5PermitsAwarded' => $euro5PermitsAwarded,
                    'euro6PermitsAwarded' => $euro6PermitsAwarded,
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                            'validityYear' => '2023'
                        ]
                    ]
                ]
            ],
            'fees' => [
                [
                    'isOutstanding' => false,
                    'isEcmtIssuingFee' => true,
                    'feeType' => [
                        'displayValue' => $feePerPermit,
                    ],
                    'grossAmount' => '400.00',
                    'invoicedDate' => '2019-07-10T00:00:00+0000'
                ]
            ],
            'totalPermitsRequired' => $permitsRequired,
        ];

        $mappedApplicationFees = [
            'issueFee' => $feePerPermit,
            'totalFee' => '400.00',
            'dueDate' => '2019-07-23',
        ];

        $dataWithMappedApplicationFees = array_merge(
            $data,
            $mappedApplicationFees
        );

        $this->applicationFees->shouldReceive('mapForDisplay')
            ->with($data)
            ->andReturn($dataWithMappedApplicationFees);

        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with(['requiredEuro5' => $euro5PermitsAwarded, 'requiredEuro6' => $euro6PermitsAwarded])
            ->andReturn(['ecmt no of permits line 1', 'ecmt no of permits line 2']);

        $this->translator->shouldReceive('translateReplace')
            ->with(
                $guidanceKey,
                [$permitsAwarded, $permitsRequired]
            )
            ->andReturn($translatedGuidance);

        $this->urlHelperService->shouldReceive('fromRoute')
            ->with(EcmtSection::ROUTE_ECMT_UNPAID_PERMITS, [], [], true)
            ->andReturn('/permits/3003/ecmt-unpaid-permits/');

        $expectedSummaryData = [
            [
                'key' => 'permits.page.fee.application.reference',
                'value' => $applicationReference,
            ],
            [
                'key' => 'permits.page.ecmt.consideration.permit.type',
                'value' => $permitTypeDescription,
            ],
            [
                'key' => 'permits.page.ecmt.consideration.permit.year',
                'value' => '2023',
            ],
            [
                'key' => 'permits.page.fee.number.permits',
                'value' => 'ecmt no of permits line 1<br>' .
                    'ecmt no of permits line 2<br>' .
                    '<a href="/permits/3003/ecmt-unpaid-permits/">View permit restrictions</a>',
                'disableHtmlEscape' => true,
            ],
            [
                'key' => 'waived.paid.permits.page.ecmt.fee-part-successful.payment.due',
                'value' => '23 Jul 2019',
            ]
        ];

        $expectedGuidanceData = [
            'value' => $translatedGuidance,
            'disableHtmlEscape' => true,
        ];

        $mappedData = $this->acceptOrDeclinePermits->mapForDisplay($data);

        $this->assertEquals(
            'waived-paid-permits.page.fee-part-successful.title',
            $mappedData['title']
        );

        $this->assertEquals(
            $expectedSummaryData,
            $mappedData['summaryData']
        );

        $this->assertEquals(
            $expectedGuidanceData,
            $mappedData['guidance']
        );
    }

    public function dpTestMapForDisplay()
    {
        return [
            [
                5,
                2,
                3,
                'markup-ecmt-fee-part-successful-hint',
                'Due to very high numbers of applications you have been awarded '.
                'with <b>5 permits</b> out of 9 you applied for.',
            ],
            [
                9,
                4,
                5,
                'markup-ecmt-fee-successful-hint',
                'You have been awarded with <b>9 permits</b> out of 9 you applied for.',
            ],
        ];
    }
}
