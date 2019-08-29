<?php

namespace PermitsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Permits\Data\Mapper\ApplicationFees;
use RuntimeException;

/**
 * ApplicationFeesTest
 */
class ApplicationFeesTest extends TestCase
{
    private $applicationFees;

    public function setUp()
    {
        $this->applicationFees = new ApplicationFees();
    }

    public function testMapForDisplay()
    {
        $issueFee = '21.55';
        $totalFee = '125.25';

        $data = [
            'fees' => [
                [
                    'isEcmtIssuingFee' => false
                ],
                [
                    'isEcmtIssuingFee' => true,
                    'feeType' => [
                        'displayValue' => $issueFee
                    ],
                    'grossAmount' => $totalFee,
                    'invoicedDate' => '2018-05-15',
                ],
                [
                    'isEcmtIssuingFee' => false
                ]
            ]
        ];

        $expectedAdditionalData = [
            'issueFee' => $issueFee,
            'totalFee' => $totalFee,
            'dueDate' => '28 May 2018'
        ];

        $expectedData = array_merge(
            $data,
            $expectedAdditionalData
        );

        $this->assertEquals(
            $expectedData,
            $this->applicationFees->mapForDisplay($data)
        );
    }

    public function testMapForDisplayNoOutstandingFees()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No outstanding issuing fees were found.');

        $data = [
            'fees' => [
                [
                    'isEcmtIssuingFee' => false
                ],
                [
                    'isEcmtIssuingFee' => false
                ],
                [
                    'isEcmtIssuingFee' => false
                ]
            ]
        ];

        $this->applicationFees->mapForDisplay($data);
    }
}
