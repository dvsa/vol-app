<?php

namespace PermitsTest\Data\Mapper;

use Permits\Data\Mapper\IrhpApplicationFeeSummary;

/**
 * IrhpApplicationFeeSummaryTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class IrhpApplicationFeeSummaryTest extends \PHPUnit\Framework\TestCase
{
    public function testMapForDisplay()
    {
        $applicationRef = 'OB1234567/1';
        $dateReceived = '2020-12-25';
        $permitTypeDesc = 'permit type description';
        $permitsRequired = 999;
        $fee = 123.45;

        $formattedDateReceived = '25 December 2020';

        $inputData = [
            'applicationRef' => $applicationRef,
            'dateReceived' => $dateReceived,
            'irhpPermitType' => [
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
                    'value' => $fee,
                    'isCurrency' => true
                ],
            ],
        ];

        $expectedOutput = $inputData + $mappedData;

        self::assertEquals($expectedOutput, IrhpApplicationFeeSummary::mapForDisplay($inputData));
    }
}
