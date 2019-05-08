<?php

namespace PermitsTest\Data\Mapper;

use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Common\Service\Helper\TranslationHelperService;
use Zend\Mvc\Controller\Plugin\Url;
use Mockery as m;

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
}
