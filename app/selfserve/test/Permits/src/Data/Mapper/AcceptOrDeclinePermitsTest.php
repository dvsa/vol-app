<?php

namespace PermitsTest\Data\Mapper;

use Permits\Data\Mapper\AcceptOrDeclinePermits;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

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

        $inputData = [
            'fees' => [
                [
                    'isOutstanding' => false,
                    'isEcmtIssuingFee' => true
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
            'irhpPermitApplications' => [
                [
                    'permitsAwarded' => $permitsAwarded,
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                           'validFrom' => '1999-03-10',
                           'validTo' => '2020-03-10'
                        ]
                    ]
                ]
            ]
        ];

        $outputData = $inputData;
        $outputData['validityPeriod']['fromDate'] = '10 Mar 1999';
        $outputData['validityPeriod']['toDate'] = '10 Mar 2020';
        $outputData['issuingFee'] = $permitsAwarded . ' x ' . '£' . $feeDisplayValue;
        $outputData['issuingFeeTotal'] = '£' . $feeGrossAmount;
        $outputData['dueDate'] = '20 Mar 2018'; //invoiced date +10 days
        $outputData['issueFee'] = $feeDisplayValue;
        $outputData['totalFee'] = $feeGrossAmount;

        self::assertEquals($outputData, AcceptOrDeclinePermits::mapForDisplay($inputData));
    }
}
