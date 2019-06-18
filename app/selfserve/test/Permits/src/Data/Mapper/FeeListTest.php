<?php

namespace PermitsTest\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Permits\Data\Mapper\FeeList;
use Zend\Mvc\Controller\Plugin\Url;

class FeeListTest extends MockeryTestCase
{
    public function testMapForDisplay()
    {
        $inputData = [
            'application' => [
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
            ],
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
                'value' => '4 permits for Euro 5 minimum emission standard<br/>7 permits for Euro 6 minimum emission standard',
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

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                ['£110']
            )
            ->andReturn('£110 (non-refundable)');
        $translator->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro5')
            ->andReturn('Euro 5 minimum emission standard');
        $translator->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro6')
            ->andReturn('Euro 6 minimum emission standard');
        $translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [4, 'Euro 5 minimum emission standard']
            )
            ->andReturn('4 permits for Euro 5 minimum emission standard');
        $translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.multiple',
                [7, 'Euro 6 minimum emission standard']
            )
            ->andReturn('7 permits for Euro 6 minimum emission standard');

        $url = m::mock(Url::class);

        $returnedData = FeeList::mapForDisplay($inputData, $translator, $url);

        self::assertEquals(
            $expectedSummaryData,
            $returnedData['summaryData']
        );
    }

    public function testMapForDisplaySingleAndZeroPermitCount()
    {
        $inputData = [
            'application' => [
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
                'requiredEuro5' => 1,
                'requiredEuro6' => 0,
                'totalPermitsRequired' => 1
            ],
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
                'value' => '1 permit for Euro 5 minimum emission standard',
                'disableHtmlEscape' => true
            ],
            [
                'key' => 'permits.page.fee.application.fee.per.permit',
                'value' => '10',
                'isCurrency' => true
            ],
            [
                'key' => 'permits.page.fee.permit.fee.total',
                'value' => '£10 (non-refundable)'
            ]
        ];

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.permit.fee.non-refundable',
                ['£10']
            )
            ->andReturn('£10 (non-refundable)');
        $translator->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro5')
            ->andReturn('Euro 5 minimum emission standard');
        $translator->shouldReceive('translateReplace')
            ->with(
                'permits.page.fee.number.permits.line.single',
                [1, 'Euro 5 minimum emission standard']
            )
            ->andReturn('1 permit for Euro 5 minimum emission standard');

        $url = m::mock(Url::class);

        $returnedData = FeeList::mapForDisplay($inputData, $translator, $url);

        self::assertEquals(
            $expectedSummaryData,
            $returnedData['summaryData']
        );
    }
}
