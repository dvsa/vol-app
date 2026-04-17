<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\View\Helper\CurrencyFormatter;
use Common\View\Helper\Status as StatusFormatter;
use Mockery as m;
use Permits\Data\Mapper\EcmtNoOfPermits;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Permits\View\Helper\IrhpApplicationSection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * IrhpApplicationFeeSummaryTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class IrhpApplicationFeeSummaryTest extends TestCase
{
    private $translationHelperService;

    private $ecmtNoOfPermits;

    private $statusFormatter;

    private $currencyFormatter;

    private $urlHelperService;

    private $irhpApplicationFeeSummary;

    public function setUp(): void
    {
        $this->translationHelperService = m::mock(TranslationHelperService::class);

        $this->ecmtNoOfPermits = m::mock(EcmtNoOfPermits::class);

        $this->statusFormatter = m::mock(StatusFormatter::class);

        $this->currencyFormatter = m::mock(CurrencyFormatter::class);

        $this->urlHelperService = m::mock(UrlHelperService::class);

        $this->irhpApplicationFeeSummary = new IrhpApplicationFeeSummary(
            $this->translationHelperService,
            $this->ecmtNoOfPermits,
            $this->statusFormatter,
            $this->currencyFormatter,
            $this->urlHelperService
        );
    }

    public function testMapForDisplayBilateralNothingPaid(): void
    {
        $applicationRef = 'OB1234567/1';
        $dateReceived = '2020-12-25';
        $permitTypeDesc = 'permit type description';
        $permitsRequired = 99;

        $fee = 87;
        $formattedFee = '£87';
        $translatedFormattedFee = '£87 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($fee)
            ->andReturn($formattedFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $formattedFee
                ]
            )
            ->andReturn(
                $translatedFormattedFee
            )
            ->once();

        $formattedDateReceived = '25 December 2020';

        $inputData = [
            'application' => [
                'applicationRef' => $applicationRef,
                'dateReceived' => $dateReceived,
                'irhpPermitType' => [
                    'id' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'name' => [
                        'description' => 'permit type description',
                    ],
                ],
                'permitsRequired' => $permitsRequired,
                'outstandingFeeAmount' => $fee
            ],
            'feeBreakdown' => [
                [
                    'total' => 50
                ],
                [
                    'total' => 12
                ],
                [
                    'total' => 25
                ]
            ],
        ];

        $mappedData = [
            'application' => [
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_DATE_HEADING,
                        'value' => $formattedDateReceived,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $permitsRequired,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::FEE_TOTAL_HEADING,
                        'value' => $translatedFormattedFee,
                    ],
                ],
                'prependTitle' => $permitTypeDesc
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    public function testMapForDisplayBilateralPartiallyPaid(): void
    {
        $applicationRef = 'OB1234567/1';
        $dateReceived = '2020-12-25';
        $permitTypeDesc = 'permit type description';
        $permitsRequired = 99;

        $paidFee = 37;
        $formattedPaidFee = '£37';
        $translatedFormattedPaidFee = '£37 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($paidFee)
            ->andReturn($formattedPaidFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $formattedPaidFee
                ]
            )
            ->andReturn(
                $translatedFormattedPaidFee
            )
            ->once();

        $remainingFee = 50;
        $formattedRemainingFee = '£50';
        $translatedFormattedRemainingFee = '£50 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($remainingFee)
            ->andReturn($formattedRemainingFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $formattedRemainingFee
                ]
            )
            ->andReturn(
                $translatedFormattedRemainingFee
            )
            ->once();

        $formattedDateReceived = '25 December 2020';

        $inputData = [
            'application' => [
                'applicationRef' => $applicationRef,
                'dateReceived' => $dateReceived,
                'irhpPermitType' => [
                    'id' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'name' => [
                        'description' => 'permit type description',
                    ],
                ],
                'permitsRequired' => $permitsRequired,
                'outstandingFeeAmount' => $remainingFee
            ],
            'feeBreakdown' => [
                [
                    'total' => 50
                ],
                [
                    'total' => 12
                ],
                [
                    'total' => 25
                ]
            ],
        ];

        $mappedData = [
            'application' => [
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_DATE_HEADING,
                        'value' => $formattedDateReceived,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $permitsRequired,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::AMOUNT_PAID_HEADING,
                        'value' => $translatedFormattedPaidFee,
                        'status' => [
                            'caption' => IrhpApplicationFeeSummary::ALREADY_PAID_STATUS,
                            'colour' => 'green'
                        ]
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::AMOUNT_REMAINING_HEADING,
                        'value' => $translatedFormattedRemainingFee,
                        'status' => [
                            'caption' => IrhpApplicationFeeSummary::TO_BE_PAID_STATUS,
                            'colour' => 'orange'
                        ]
                    ],
                ],
                'prependTitle' => $permitTypeDesc,
                'warningMessage' => 'permits.page.irhp-fee.message.part-paid',
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    public function testMapForDisplayBilateralFullyPaid(): void
    {
        $applicationRef = 'OB1234567/1';
        $dateReceived = '2020-12-25';
        $permitTypeDesc = 'permit type description';
        $permitsRequired = 99;

        $paidFee = 87;
        $formattedPaidFee = '£87';
        $translatedFormattedPaidFee = '£87 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($paidFee)
            ->andReturn($formattedPaidFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $formattedPaidFee
                ]
            )
            ->andReturn(
                $translatedFormattedPaidFee
            )
            ->once();

        $formattedDateReceived = '25 December 2020';

        $inputData = [
            'application' => [
                'applicationRef' => $applicationRef,
                'dateReceived' => $dateReceived,
                'irhpPermitType' => [
                    'id' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'name' => [
                        'description' => 'permit type description',
                    ],
                ],
                'permitsRequired' => $permitsRequired,
                'outstandingFeeAmount' => 0,
            ],
            'feeBreakdown' => [
                [
                    'total' => 50
                ],
                [
                    'total' => 12
                ],
                [
                    'total' => 25
                ]
            ],
        ];

        $mappedData = [
            'application' => [
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_DATE_HEADING,
                        'value' => $formattedDateReceived,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $permitsRequired,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::AMOUNT_PAID_HEADING,
                        'value' => $translatedFormattedPaidFee,
                        'status' => [
                            'caption' => IrhpApplicationFeeSummary::ALREADY_PAID_STATUS,
                            'colour' => 'green'
                        ]
                    ],
                ],
                'prependTitle' => $permitTypeDesc,
                'warningMessage' => 'permits.page.irhp-fee.message.total-already-paid',
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    public function testMapForDisplayMultilateral(): void
    {
        $applicationRef = 'OB1234567/1';
        $dateReceived = '2020-12-25';
        $permitTypeDesc = 'permit type description';
        $permitsRequired = 999;

        $fee = 123.45;
        $formattedFee = '£123.45';
        $translatedFormattedFee = '£123.45 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($fee)
            ->andReturn($formattedFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $formattedFee
                ]
            )
            ->andReturn(
                $translatedFormattedFee
            )
            ->once();

        $formattedDateReceived = '25 December 2020';

        $inputData = [
            'application' => [
                'applicationRef' => $applicationRef,
                'dateReceived' => $dateReceived,
                'irhpPermitType' => [
                    'id' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'name' => [
                        'description' => 'permit type description',
                    ],
                ],
                'permitsRequired' => $permitsRequired,
                'outstandingFeeAmount' => $fee
            ]
        ];

        $mappedData = [
            'application' => [
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_DATE_HEADING,
                        'value' => $formattedDateReceived,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $permitsRequired,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::FEE_TOTAL_HEADING,
                        'value' => $translatedFormattedFee,
                    ],
                ],
                'prependTitle' => $permitTypeDesc
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    public function testMapForDisplayEcmtRemoval(): void
    {
        $applicationRef = 'OB1234567/1';
        $dateReceived = '2020-12-25';
        $permitTypeId = RefData::ECMT_REMOVAL_PERMIT_TYPE_ID;
        $permitTypeDesc = 'ECMT International Removal';
        $permitsRequired = 5;
        $irfoFeePerPermit = 18;

        $fee = 90.00;
        $formattedFee = '£90';
        $translatedFormattedFee = '£90 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($fee)
            ->andReturn($formattedFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $formattedFee
                ]
            )
            ->andReturn(
                $translatedFormattedFee
            )
            ->once();

        $formattedDateReceived = '25 December 2020';

        $inputData = [
            'application' => [
                'applicationRef' => $applicationRef,
                'dateReceived' => $dateReceived,
                'irhpPermitType' => [
                    'id' => $permitTypeId,
                    'name' => [
                        'description' => $permitTypeDesc,
                    ],
                ],
                'permitsRequired' => $permitsRequired,
                'outstandingFeeAmount' => $fee,
                'fees' => [
                    [
                        'feeType' => [
                            'fixedValue' => 10,
                            'feeType' => [
                                'id' => 'OTHERTYPE1'
                            ]
                        ]
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => $irfoFeePerPermit,
                            'feeType' => [
                                'id' => RefData::IRFO_GV_FEE_TYPE,
                            ]
                        ]
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => 30,
                            'feeType' => [
                                'id' => 'OTHERTYPE1'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mappedData = [
            'application' => [
                'showFeeSummaryTitle' => true,
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_DATE_HEADING,
                        'value' => $formattedDateReceived,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::FEE_PER_PERMIT_HEADING,
                        'value' => $irfoFeePerPermit,
                        'isCurrency' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $permitsRequired,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::FEE_TOTAL_HEADING,
                        'value' => $translatedFormattedFee,
                    ],
                ],
                'prependTitle' => $permitTypeDesc
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestMapForDisplayEcmtAnnualAndShortTerm')]
    public function testMapForDisplayEcmtAnnualAndShortTerm(int $permitTypeId): void
    {
        $isUnderConsideration = false;
        $isAwaitingFee = false;
        $applicationRef = 'OB1234567/1';
        $permitTypeDesc = 'Permit type description';
        $dateReceived = '2020-12-25';
        $formattedDateReceived = '25 December 2020';

        $stockValidTo = '2022-12-31';
        $permitPeriod = 'period.value';

        $formattedNoOfPermitsRequiredLine1 = '1 permit for Euro 5 minimum emission standard';
        $formattedNoOfPermitsRequiredLine2 = '5 permits for Euro 6 minimum emission standard';
        $formattedNoOfPermitsRequired = '1 permit for Euro 5 minimum emission standard<br>' .
            '5 permits for Euro 6 minimum emission standard';

        $irhpPermitApplicationInputData = [
            'requiredEuro5' => 1,
            'requiredEuro6' => 5,
            'irhpPermitWindow' => [
                'irhpPermitStock' => [
                    'periodNameKey' => $permitPeriod,
                    'validTo' => $stockValidTo
                ]
            ]
        ];

        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with($irhpPermitApplicationInputData)
            ->andReturn([$formattedNoOfPermitsRequiredLine1, $formattedNoOfPermitsRequiredLine2]);

        $appFeePerPermit = 20;
        $totalApplicationFee = '120';
        $formattedTotalApplicationFee = '£120';
        $translatedFormattedTotalApplicationFee = '£120 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($totalApplicationFee)
            ->andReturn($formattedTotalApplicationFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [$formattedTotalApplicationFee]
            )
            ->andReturn($translatedFormattedTotalApplicationFee);

        $inputData = [
            'application' => [
                'businessProcess' => [
                    'id' => RefData::BUSINESS_PROCESS_APGG
                ],
                'canViewCandidatePermits' => false,
                'isUnderConsideration' => $isUnderConsideration,
                'isAwaitingFee' => $isAwaitingFee,
                'applicationRef' => $applicationRef,
                'dateReceived' => $dateReceived,
                'irhpPermitType' => [
                    'id' => $permitTypeId,
                    'name' => [
                        'description' => $permitTypeDesc
                    ],
                ],
                'irhpPermitApplications' => [$irhpPermitApplicationInputData],
                'fees' => [
                    [
                        'feeType' => [
                            'fixedValue' => 10,
                            'feeType' => [
                                'id' => 'OTHERTYPE1'
                            ]
                        ]
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => $appFeePerPermit,
                            'feeType' => [
                                'id' => RefData::IRHP_GV_APPLICATION_FEE_TYPE,
                            ]
                        ]
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => 30,
                            'feeType' => [
                                'id' => 'OTHERTYPE1'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mappedData = [
            'application' => [
                'showFeeSummaryTitle' => true,
                'warningMessage' => 'permits.page.irhp-fee.message',
                'guidance' => [],
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_PERIOD_HEADING,
                        'value' => $permitPeriod,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_DATE_HEADING,
                        'value' => $formattedDateReceived,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $formattedNoOfPermitsRequired,
                        'disableHtmlEscape' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_FEE_PER_PERMIT_HEADING,
                        'value' => $appFeePerPermit,
                        'isCurrency' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::TOTAL_APPLICATION_FEE_HEADING,
                        'value' => $translatedFormattedTotalApplicationFee,
                    ],
                ],
                'prependTitle' => $permitTypeDesc
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    /**
     * @return int[][]
     *
     * @psalm-return list{list{1}, list{2}}
     */
    public static function dpTestMapForDisplayEcmtAnnualAndShortTerm(): array
    {
        return [
            [RefData::ECMT_PERMIT_TYPE_ID],
            [RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID],
        ];
    }

    public function testMapForDisplayEcmtShortTermUnderConsideration(): void
    {
        $isUnderConsideration = true;
        $isAwaitingFee = false;
        $status = [
            'id' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
            'description' => 'Under Consideration'
        ];
        $applicationRef = 'OB1234567/1';
        $permitTypeDesc = 'Short-term ECMT';
        $dateReceived = '2020-12-25';
        $formattedDateReceived = '25 December 2020';

        $permitYear = 2022;

        $formattedNoOfPermitsRequiredLine1 = '1 permit for Euro 5 minimum emission standard';
        $formattedNoOfPermitsRequiredLine2 = '5 permits for Euro 6 minimum emission standard';
        $formattedNoOfPermitsRequired = '1 permit for Euro 5 minimum emission standard<br>' .
            '5 permits for Euro 6 minimum emission standard';

        $irhpPermitApplicationInputData = [
            'requiredEuro5' => 1,
            'requiredEuro6' => 5,
            'irhpPermitWindow' => [
                'irhpPermitStock' => [
                    'validityYear' => $permitYear
                ]
            ]
        ];

        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with($irhpPermitApplicationInputData)
            ->andReturn([$formattedNoOfPermitsRequiredLine1, $formattedNoOfPermitsRequiredLine2]);

        $appFeePerPermit = 20;

        $totalApplicationFee = '120';
        $formattedTotalApplicationFee = '£120';
        $translatedFormattedTotalApplicationFee = '£120 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($totalApplicationFee)
            ->andReturn($formattedTotalApplicationFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [$formattedTotalApplicationFee]
            )
            ->andReturn($translatedFormattedTotalApplicationFee);

        $formattedStatus = '<span class="status orange">Under Consideration</span>';
        $this->statusFormatter->shouldReceive('__invoke')
            ->with($status)
            ->andReturn($formattedStatus);

        $inputData = [
            'application' => [
                'businessProcess' => [
                    'id' => RefData::BUSINESS_PROCESS_APGG
                ],
                'canViewCandidatePermits' => false,
                'isUnderConsideration' => $isUnderConsideration,
                'isAwaitingFee' => $isAwaitingFee,
                'status' => $status,
                'applicationRef' => $applicationRef,
                'dateReceived' => $dateReceived,
                'irhpPermitType' => [
                    'id' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'name' => [
                        'description' => $permitTypeDesc
                    ],
                ],
                'irhpPermitApplications' => [$irhpPermitApplicationInputData],
                'fees' => [
                    [
                        'feeType' => [
                            'fixedValue' => 10,
                            'feeType' => [
                                'id' => 'OTHERTYPE1'
                            ]
                        ]
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => $appFeePerPermit,
                            'feeType' => [
                                'id' => RefData::IRHP_GV_APPLICATION_FEE_TYPE,
                            ]
                        ]
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => 30,
                            'feeType' => [
                                'id' => 'OTHERTYPE1'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mappedData = [
            'application' => [
                'showFeeSummaryTitle' => true,
                'warningMessage' => 'permits.page.irhp-fee.message',
                'guidance' => [],
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_STATUS_HEADING,
                        'value' => $formattedStatus,
                        'disableHtmlEscape' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_YEAR_HEADING,
                        'value' => $permitYear,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_DATE_HEADING,
                        'value' => $formattedDateReceived,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $formattedNoOfPermitsRequired,
                        'disableHtmlEscape' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::TOTAL_APPLICATION_FEE_PAID_HEADING,
                        'value' => $translatedFormattedTotalApplicationFee,
                    ],
                ],
                'prependTitle' => $permitTypeDesc
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    public function testMapForDisplayEcmtShortTermAwaitingFee(): void
    {
        $isUnderConsideration = false;
        $isAwaitingFee = true;
        $applicationRef = 'OB1234567/1';
        $permitTypeDesc = 'Short-term ECMT';
        $feeDueDate = '2020-12-25';
        $formattedFeeDueDate = '25 December 2020';

        $permitYear = 2022;

        $formattedNoOfPermitsRequiredLine1 = '1 permit for Euro 5 minimum emission standard';
        $formattedNoOfPermitsRequiredLine2 = '5 permits for Euro 6 minimum emission standard';
        $formattedNoOfPermitsRequired = '1 permit for Euro 5 minimum emission standard<br>' .
            '5 permits for Euro 6 minimum emission standard';

        $irhpPermitApplicationInputData = [
            'euro5PermitsWanted' => 1,
            'euro6PermitsWanted' => 5,
            'irhpPermitWindow' => [
                'irhpPermitStock' => [
                    'validityYear' => $permitYear
                ]
            ]
        ];

        $ecmtNoOfPermitsInputData = [
            'requiredEuro5' => 1,
            'requiredEuro6' => 5
        ];

        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with($ecmtNoOfPermitsInputData)
            ->andReturn([$formattedNoOfPermitsRequiredLine1, $formattedNoOfPermitsRequiredLine2]);

        $issueFeePerPermit = 20;
        $totalApplicationFee = '120';
        $formattedTotalApplicationFee = '£120';
        $translatedFormattedTotalApplicationFee = '£120 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($totalApplicationFee)
            ->andReturn($formattedTotalApplicationFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [$formattedTotalApplicationFee]
            )
            ->andReturn($translatedFormattedTotalApplicationFee);

        $inputData = [
            'application' => [
                'businessProcess' => [
                    'id' => RefData::BUSINESS_PROCESS_APGG
                ],
                'canViewCandidatePermits' => false,
                'isUnderConsideration' => $isUnderConsideration,
                'isAwaitingFee' => $isAwaitingFee,
                'applicationRef' => $applicationRef,
                'irhpPermitType' => [
                    'id' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'name' => [
                        'description' => $permitTypeDesc
                    ],
                ],
                'irhpPermitApplications' => [$irhpPermitApplicationInputData],
                'fees' => [
                    [
                        'feeType' => [
                            'fixedValue' => 10,
                            'feeType' => [
                                'id' => 'OTHERTYPE1'
                            ]
                        ],
                        'feeStatus' => [
                            'id' => RefData::FEE_STATUS_PAID
                        ]
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => $issueFeePerPermit,
                            'feeType' => [
                                'id' => RefData::IRHP_GV_ISSUE_FEE_TYPE,
                            ]
                        ],
                        'feeStatus' => [
                            'id' => RefData::FEE_STATUS_OUTSTANDING
                        ],
                        'dueDate' => $feeDueDate,
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => $issueFeePerPermit,
                            'feeType' => [
                                'id' => RefData::IRHP_GV_ISSUE_FEE_TYPE,
                            ]
                        ],
                        'feeStatus' => [
                            'id' => RefData::FEE_STATUS_CANCELLED
                        ],
                        'dueDate' => null,
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => 30,
                            'feeType' => [
                                'id' => RefData::IRHP_GV_APPLICATION_FEE_TYPE,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mappedData = [
            'application' => [
                'showFeeSummaryTitle' => true,
                'warningMessage' => 'permits.page.irhp-fee.message',
                'guidance' => [],
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_YEAR_HEADING,
                        'value' => $permitYear,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $formattedNoOfPermitsRequired,
                        'disableHtmlEscape' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::ISSUE_FEE_PER_PERMIT_HEADING,
                        'value' => $issueFeePerPermit,
                        'isCurrency' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::TOTAL_ISSUE_FEE_HEADING,
                        'value' => $translatedFormattedTotalApplicationFee,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PAYMENT_DUE_DATE_HEADING,
                        'value' => $formattedFeeDueDate,
                    ],
                ],
                'prependTitle' => $permitTypeDesc
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    public function testMapForDisplayEcmtShortTermCanViewCandidatePermits(): void
    {
        $isUnderConsideration = false;
        $isAwaitingFee = true;
        $applicationRef = 'OB1234567/1';
        $permitTypeDesc = 'Short-term ECMT';
        $feeDueDate = '2020-12-25';
        $formattedFeeDueDate = '25 December 2020';
        $totalPermitsRequired = 10;
        $totalPermitsAwarded = 10;

        $permitYear = 2022;

        $formattedNoOfPermitsRequiredLine1 = '1 permit for Euro 5 minimum emission standard';
        $formattedNoOfPermitsRequiredLine2 = '5 permits for Euro 6 minimum emission standard';
        $formattedNoOfPermitsRequired = '1 permit for Euro 5 minimum emission standard<br>'
            . '5 permits for Euro 6 minimum emission standard';

        $irhpPermitApplicationInputData = [
            'euro5PermitsWanted' => 1,
            'euro6PermitsWanted' => 5,
            'irhpPermitWindow' => [
                'irhpPermitStock' => [
                    'validityYear' => $permitYear
                ]
            ]
        ];

        $ecmtNoOfPermitsInputData = [
            'requiredEuro5' => 1,
            'requiredEuro6' => 5
        ];

        $this->ecmtNoOfPermits->shouldReceive('mapForDisplay')
            ->with($ecmtNoOfPermitsInputData)
            ->andReturn([$formattedNoOfPermitsRequiredLine1, $formattedNoOfPermitsRequiredLine2]);

        $issueFeePerPermit = 20;
        $totalApplicationFee = '120';
        $formattedTotalApplicationFee = '£120';
        $translatedFormattedTotalApplicationFee = '£120 (non-refundable)';

        $this->currencyFormatter->shouldReceive('__invoke')
            ->with($totalApplicationFee)
            ->andReturn($formattedTotalApplicationFee);

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [$formattedTotalApplicationFee]
            )
            ->andReturn($translatedFormattedTotalApplicationFee)
            ->shouldReceive('translateReplace')
            ->with(
                'markup-ecmt-fee-successful-hint',
                [$totalPermitsAwarded, $totalPermitsRequired]
            )
            ->andReturn('translated-markup-ecmt-fee-successful-hint')
            ->shouldReceive('translate')
            ->with('permits.page.view.permit.restrictions')
            ->andReturn('UNPAID_PERMITS_LABEL');

        $this->urlHelperService->shouldReceive('fromRoute')
            ->with(IrhpApplicationSection::ROUTE_UNPAID_PERMITS, [], [], true)
            ->andReturn('UNPAID_PERMITS_URL');

        $inputData = [
            'application' => [
                'businessProcess' => [
                    'id' => RefData::BUSINESS_PROCESS_APSG
                ],
                'canViewCandidatePermits' => true,
                'totalPermitsRequired' => $totalPermitsRequired,
                'totalPermitsAwarded' => $totalPermitsAwarded,
                'isUnderConsideration' => $isUnderConsideration,
                'isAwaitingFee' => $isAwaitingFee,
                'applicationRef' => $applicationRef,
                'irhpPermitType' => [
                    'id' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'name' => [
                        'description' => $permitTypeDesc
                    ],
                ],
                'irhpPermitApplications' => [$irhpPermitApplicationInputData],
                'fees' => [
                    [
                        'feeType' => [
                            'fixedValue' => 10,
                            'feeType' => [
                                'id' => 'OTHERTYPE1'
                            ]
                        ],
                        'feeStatus' => [
                            'id' => RefData::FEE_STATUS_PAID
                        ]
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => $issueFeePerPermit,
                            'feeType' => [
                                'id' => RefData::IRHP_GV_ISSUE_FEE_TYPE,
                            ]
                        ],
                        'feeStatus' => [
                            'id' => RefData::FEE_STATUS_OUTSTANDING
                        ],
                        'dueDate' => $feeDueDate,
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => $issueFeePerPermit,
                            'feeType' => [
                                'id' => RefData::IRHP_GV_ISSUE_FEE_TYPE,
                            ]
                        ],
                        'feeStatus' => [
                            'id' => RefData::FEE_STATUS_CANCELLED
                        ],
                        'dueDate' => null,
                    ],
                    [
                        'feeType' => [
                            'fixedValue' => 30,
                            'feeType' => [
                                'id' => RefData::IRHP_GV_APPLICATION_FEE_TYPE,
                            ]
                        ]
                    ]
                ],
            ],
            'prependTitle' => $permitTypeDesc
        ];

        $mappedData = [
            'application' => [
                'showFeeSummaryTitle' => true,
                'warningMessage' => 'permits.page.irhp-fee.message',
                'guidance' => [
                    'value' => 'translated-markup-ecmt-fee-successful-hint',
                    'disableHtmlEscape' => true,
                ],
                'mappedFeeData' => [
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_TYPE_HEADING,
                        'value' => $permitTypeDesc,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PERMIT_YEAR_HEADING,
                        'value' => $permitYear,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::APP_REFERENCE_HEADING,
                        'value' => $applicationRef,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::NUM_PERMITS_HEADING,
                        'value' => $formattedNoOfPermitsRequired,
                        'disableHtmlEscape' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::ISSUE_FEE_PER_PERMIT_HEADING,
                        'value' => $issueFeePerPermit,
                        'isCurrency' => true,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::TOTAL_ISSUE_FEE_HEADING,
                        'value' => $translatedFormattedTotalApplicationFee,
                    ],
                    [
                        'key' => IrhpApplicationFeeSummary::PAYMENT_DUE_DATE_HEADING,
                        'value' => $formattedFeeDueDate,
                    ],
                ],
                'prependTitle' => $permitTypeDesc
            ]
        ];

        $expectedOutput = array_merge_recursive($inputData, $mappedData);

        $this->assertEquals(
            $expectedOutput,
            $this->irhpApplicationFeeSummary->mapForDisplay($inputData)
        );
    }

    public function testMapForDisplayUnsupportedException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported permit type id 57');

        $inputData = [
            'application' => [
                'irhpPermitType' => [
                    'id' => 57
                ]
            ]
        ];

        $this->irhpApplicationFeeSummary->mapForDisplay($inputData);
    }
}
