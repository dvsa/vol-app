<?php

namespace PermitsTest\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Permits\Data\Mapper\AcceptOrDeclinePermits;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * AcceptOrDeclinePermitsTest
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.com>
 */
class AcceptOrDeclinePermitsTest extends TestCase
{
    public function testMapForDisplay()
    {
        $feeDisplayValue = '100';
        $feeGrossAmount = '200';
        $permitsAwarded = 5;

        $translationHelperService = m::mock(TranslationHelperService::class);
        $translationHelperService
            ->shouldReceive('translateReplace')
            ->andReturn('6 x £123 (per permit) <a class="govuk-link govuk-!-display-block" href="/permits/2/ecmt-unpaid-permits/">View Permits</a>')
            ->once()
            ->shouldReceive('translateReplace')
            ->andReturn('£123 (non-refundable)')
            ->once()
            ->shouldReceive('translateReplace')
            ->andReturn('01 Jan 2029 to 31 Dec 2029')
            ->once()
            ->shouldReceive('translateReplace')
            ->andReturn('Due to very high numbers of applications you have been awarded with <b>6 permits</b> out of 8 you applied for.')
            ->once();



        $url = m::mock(Url::class);
        $url->shouldReceive('fromRoute');

        $inputData = [
            'fees' => [
                [
                    'isOutstanding' => false,
                    'isEcmtIssuingFee' => true,
                    'grossAmount' => $feeGrossAmount,
                    'feeType' => [
                        'displayValue' => $feeDisplayValue
                    ],
                    'invoicedDate' => '2018-03-10'
                ],
                [
                    'isOutstanding' => true,
                    'isEcmtIssuingFee' => false
                ],
                [
                    'isOutstanding' => false,
                    'isEcmtIssuingFee' => false
                ],
                [
                    //Only fee that should be used
                    'isOutstanding' => true,
                    'isEcmtIssuingFee' => true,
                    'grossAmount' => $feeGrossAmount,
                    'feeType' => [
                        'displayValue' => $feeDisplayValue
                    ],
                    'invoicedDate' => '2018-03-10'
                ]
            ],
            'hasOutstandingFees' => true,
            'irhpPermitApplications' => [
                [
                    'permitsAwarded' => $permitsAwarded,
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                           'validFrom' => '2029-01-01',
                           'validTo' => '2029-12-31'
                        ]
                    ]
                ]
            ],
            'applicationRef' => 'OG7654321 / 2',
            'permitType' => [
                'description' => 'Annual ECMT'
            ],
            'permitsRequired' => 2
        ];
        $outputData['validityPeriod']['fromDate'] = '921024000';
        $outputData['validityPeriod']['toDate'] = '1583798400';
        $outputData = $inputData;
        $outputData['title'] = 'permits.page.fee-part-successful.title';
        $outputData['dueDate'] = '20 Mar 2018'; //invoiced date +10 days
        $outputData['issueFee'] = $feeDisplayValue;
        $outputData['totalFee'] = $feeGrossAmount;
        $outputData['summaryData'] = [
            0 => [
                'key' => 'permits.page.ecmt.consideration.reference.number',
                'value' => 'OG7654321 / 2'
            ],
            1 => [
                'key' => 'permits.page.ecmt.consideration.permit.type',
                'value' => 'Annual ECMT'
            ],
            2 => [
                'key' => 'permits.page.ecmt.fee-part-successful.permit.validity',
                'value' => '01 Jan 2029 to 31 Dec 2029'
            ],
            3 => [
                'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee',
                'value' => '6 x £123 (per permit) <a class="govuk-link govuk-!-display-block" href="/permits/2/ecmt-unpaid-permits/">View Permits</a>',
                'disableHtmlEscape' => true
            ],
            4 => [
                'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee.total',
                'value' => '£123 (non-refundable)'
            ],
            5 => [
                'key' => 'permits.page.ecmt.fee-part-successful.payment.due',
                'value' => '20 Mar 2018'
            ]
        ];
        $outputData['guidance'] = [
            'value' => 'Due to very high numbers of applications you have been awarded with <b>6 permits</b> out of 8 you applied for.',
            'disableHtmlEscape' => true,
        ];

        self::assertEquals($outputData, AcceptOrDeclinePermits::mapForDisplay($inputData, $translationHelperService, $url));
    }
}
