<?php

namespace PermitsTest\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Permits\Data\Mapper\AcceptOrDeclinePermits;
use Permits\View\Helper\EcmtSection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * AcceptOrDeclinePermitsTest
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.com>
 */
class AcceptOrDeclinePermitsTest extends MockeryTestCase
{
    private $url;

    private $translator;

    public function setUp()
    {
        $this->url = m::mock(Url::class);

        $this->url->shouldReceive('fromRoute')
            ->with(EcmtSection::ROUTE_ECMT_UNPAID_PERMITS, [], [], true)
            ->andReturn('/permits/3003/ecmt-unpaid-permits/');

        $this->translator = m::mock(TranslationHelperService::class);

        $this->translator->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro5')
            ->andReturn('Euro 5 minimum emission standard');

        $this->translator->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro6')
            ->andReturn('Euro 6 minimum emission standard');

        $this->translator->shouldReceive('translate')
            ->with('permits.page.ecmt.fee-part-successful.view.permit.restrictions')
            ->andReturn('View permit restrictions');
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
            'hasOutstandingFees' => 1,
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

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [$euro5PermitsAwarded, 'Euro 5 minimum emission standard']
            )
            ->andReturn('2 permits for Euro 5 minimum emission standard');

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [$euro6PermitsAwarded, 'Euro 6 minimum emission standard']
            )
            ->andReturn('3 permits for Euro 6 minimum emission standard');

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
                'value' => '2 permits for Euro 5 minimum emission standard<br>' .
                    '3 permits for Euro 6 minimum emission standard<br>' .
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

        $mappedData = AcceptOrDeclinePermits::mapForDisplay($data, $this->translator, $this->url);

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
            'hasOutstandingFees' => 0,
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

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [$euro5PermitsAwarded, 'Euro 5 minimum emission standard']
            )
            ->andReturn('2 permits for Euro 5 minimum emission standard');

        $this->translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [$euro6PermitsAwarded, 'Euro 6 minimum emission standard']
            )
            ->andReturn('3 permits for Euro 6 minimum emission standard');

        $this->translator->shouldReceive('translateReplace')
            ->with(
                $guidanceKey,
                [$permitsAwarded, $permitsRequired]
            )
            ->andReturn($translatedGuidance);

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
                'value' => '2 permits for Euro 5 minimum emission standard<br>' .
                    '3 permits for Euro 6 minimum emission standard<br>' .
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

        $mappedData = AcceptOrDeclinePermits::mapForDisplay($data, $this->translator, $this->url);

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
