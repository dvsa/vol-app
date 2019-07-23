<?php

namespace PermitsTest\Data\Mapper;

use Common\RefData;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Common\Service\Helper\TranslationHelperService;
use Zend\Mvc\Controller\Plugin\Url;
use Mockery as m;
use RuntimeException;

/**
 * IrhpApplicationFeeSummaryTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class IrhpApplicationFeeSummaryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dpTestMapForDisplayBilateralMultilateral
     */
    public function testMapForDisplayBilateralMultilateral($permitTypeId)
    {
        $applicationRef = 'OB1234567/1';
        $dateReceived = '2020-12-25';
        $permitTypeDesc = 'permit type description';
        $permitsRequired = 999;

        $fee = 123.45;
        $formattedFee = '£123.45';
        $translatedFormattedFee = '£123.45 (non-refundable)';

        $url = m::mock(Url::class);
        $translationHelperService = m::mock(TranslationHelperService::class);
        $translationHelperService
            ->shouldReceive('translateReplace')
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
            'applicationRef' => $applicationRef,
            'dateReceived' => $dateReceived,
            'irhpPermitType' => [
                'id' => $permitTypeId,
                'name' => [
                    'description' => $permitTypeDesc,
                ],
            ],
            'permitsRequired' => $permitsRequired,
            'outstandingFeeAmount' => $fee
        ];

        $mappedData = [
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
        ];

        $expectedOutput = $inputData + $mappedData;

        self::assertEquals(
            $expectedOutput,
            IrhpApplicationFeeSummary::mapForDisplay($inputData, $translationHelperService, $url)
        );
    }

    public function dpTestMapForDisplayBilateralMultilateral()
    {
        return [
            [RefData::IRHP_BILATERAL_PERMIT_TYPE_ID],
            [RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID],
            [RefData::ECMT_REMOVAL_PERMIT_TYPE_ID],
        ];
    }

    public function testMapForDisplayEcmtShortTerm()
    {
        $url = m::mock(Url::class);
        $translationHelperService = m::mock(TranslationHelperService::class);

        $isUnderConsideration = false;
        $applicationRef = 'OB1234567/1';
        $permitTypeDesc = 'Short-term ECMT';
        $dateReceived = '2020-12-25';
        $formattedDateReceived = '25 December 2020';

        $stockValidTo = '2022-12-31';
        $permitYear = 2022;

        $requiredEuro5 = 1;
        $requiredEuro6 = 5;
        $formattedNoOfPermitsRequiredLine1 = '1 permit for Euro 5 minimum emission standard';
        $formattedNoOfPermitsRequiredLine2 = '5 permits for Euro 6 minimum emission standard';
        $formattedNoOfPermitsRequired = '1 permit for Euro 5 minimum emission standard<br>' .
            '5 permits for Euro 6 minimum emission standard';

        $euro5CategoryName = 'Euro 5 minimum emission standard';
        $translationHelperService->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro5')
            ->andReturn($euro5CategoryName);

        $euro6CategoryName = 'Euro 6 minimum emission standard';
        $translationHelperService->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro6')
            ->andReturn($euro6CategoryName);

        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.single',
                [1, $euro5CategoryName]
            )
            ->andReturn($formattedNoOfPermitsRequiredLine1);
        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [5, $euro6CategoryName]
            )
            ->andReturn($formattedNoOfPermitsRequiredLine2);

        $appFeePerPermit = 20;

        $formattedTotalApplicationFee = '£120';
        $translatedFormattedTotalApplicationFee = '£120 (non-refundable)';

        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [$formattedTotalApplicationFee]
            )
            ->andReturn($translatedFormattedTotalApplicationFee);

        $inputData = [
            'isUnderConsideration' => $isUnderConsideration,
            'applicationRef' => $applicationRef,
            'dateReceived' => $dateReceived,
            'irhpPermitType' => [
                'id' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                'name' => [
                    'description' => $permitTypeDesc
                ],
            ],
            'irhpPermitApplications' => [
                [
                    'requiredEuro5' => $requiredEuro5,
                    'requiredEuro6' => $requiredEuro6,
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                            'validTo' => $stockValidTo
                        ]
                    ]
                ]
            ],
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
                            'id' => 'IRHPGVAPP'
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
        ];

        $mappedData = [
            'showFeeSummaryTitle' => true,
            'showWarningMessage' => true,
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
            ]
        ];

        $expectedOutput = $inputData + $mappedData;

        self::assertEquals(
            $expectedOutput,
            IrhpApplicationFeeSummary::mapForDisplay($inputData, $translationHelperService, $url)
        );
    }

    public function testMapForDisplayEcmtShortTermUnderConsideration()
    {
        $url = m::mock(Url::class);
        $translationHelperService = m::mock(TranslationHelperService::class);

        $isUnderConsideration = true;
        $status = [
            'id' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
            'description' => 'Under Consideration'
        ];
        $applicationRef = 'OB1234567/1';
        $permitTypeDesc = 'Short-term ECMT';
        $dateReceived = '2020-12-25';
        $formattedDateReceived = '25 December 2020';

        $stockValidTo = '2022-12-31';
        $permitYear = 2022;

        $requiredEuro5 = 1;
        $requiredEuro6 = 5;
        $formattedNoOfPermitsRequiredLine1 = '1 permit for Euro 5 minimum emission standard';
        $formattedNoOfPermitsRequiredLine2 = '5 permits for Euro 6 minimum emission standard';
        $formattedNoOfPermitsRequired = '1 permit for Euro 5 minimum emission standard<br>' .
            '5 permits for Euro 6 minimum emission standard';

        $euro5CategoryName = 'Euro 5 minimum emission standard';
        $translationHelperService->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro5')
            ->andReturn($euro5CategoryName);

        $euro6CategoryName = 'Euro 6 minimum emission standard';
        $translationHelperService->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro6')
            ->andReturn($euro6CategoryName);

        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.single',
                [1, $euro5CategoryName]
            )
            ->andReturn($formattedNoOfPermitsRequiredLine1);
        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [5, $euro6CategoryName]
            )
            ->andReturn($formattedNoOfPermitsRequiredLine2);

        $appFeePerPermit = 20;

        $formattedTotalApplicationFee = '£120';
        $translatedFormattedTotalApplicationFee = '£120 (non-refundable)';

        $translationHelperService->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                [$formattedTotalApplicationFee]
            )
            ->andReturn($translatedFormattedTotalApplicationFee);

        $inputData = [
            'isUnderConsideration' => $isUnderConsideration,
            'status' => $status,
            'applicationRef' => $applicationRef,
            'dateReceived' => $dateReceived,
            'irhpPermitType' => [
                'id' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                'name' => [
                    'description' => $permitTypeDesc
                ],
            ],
            'irhpPermitApplications' => [
                [
                    'requiredEuro5' => $requiredEuro5,
                    'requiredEuro6' => $requiredEuro6,
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                            'validTo' => $stockValidTo
                        ]
                    ]
                ]
            ],
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
                            'id' => 'IRHPGVAPP'
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
        ];

        $mappedData = [
            'showFeeSummaryTitle' => true,
            'showWarningMessage' => true,
            'mappedFeeData' => [
                [
                    'key' => IrhpApplicationFeeSummary::PERMIT_STATUS_HEADING,
                    'value' => '<span class="status orange">Under Consideration</span>',
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
            ]
        ];

        $expectedOutput = $inputData + $mappedData;

        self::assertEquals(
            $expectedOutput,
            IrhpApplicationFeeSummary::mapForDisplay($inputData, $translationHelperService, $url)
        );
    }

    public function testMapForDisplayUnsupportedException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported permit type id 57');

        $inputData = [
            'irhpPermitType' => [
                'id' => 57
            ]
        ];

        $translationHelperService = m::mock(TranslationHelperService::class);
        $url = m::mock(Url::class);
        IrhpApplicationFeeSummary::mapForDisplay($inputData, $translationHelperService, $url);
    }
}
