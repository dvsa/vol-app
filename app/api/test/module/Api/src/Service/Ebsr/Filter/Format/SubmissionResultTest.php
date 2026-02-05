<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format;

use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\SubmissionResult;
use Mockery as m;

/**
 * Class SubmissionResultTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SubmissionResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests filter
     *
     *
     * @param $submissionDate
     * @param $rawData
     * @param $expectedData
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter(mixed $submissionDate, mixed $rawData, mixed $expectedData): void
    {
        $sut = new SubmissionResult();

        $ebsrSub = m::mock(EbsrSubmissionEntity::class);
        $ebsrSub->shouldReceive('getSubmittedDate')->once()->andReturn($submissionDate);

        $errors = ['errors'];

        $value = [
            'errorMessages' => $errors,
            'ebsrSub' => $ebsrSub,
            'rawData' => $rawData
        ];

        $extraBusData = array_merge($expectedData, ['submissionErrors' => $errors]);

        $expectedOutput = [
            'errors' => $errors,
            'extra_bus_data' => $extraBusData
        ];

        $this->assertEquals($expectedOutput, $sut->filter($value));
    }

    /**
     * Data provider for testFilter
     *
     * @return array
     */
    public static function provideFilter(): array
    {
        $submissionDate = '2015-12-25 00:00:00';
        $submissionDateTime = new \DateTime($submissionDate);
        $formattedSubmissionDate = $submissionDateTime->format(SubmissionResult::DATE_FORMAT);

        $blankExpectedData = [
            'submissionDate' => $formattedSubmissionDate,
            'registrationNumber' => SubmissionResult::UNKNOWN_REG_NO,
            'origin' => SubmissionResult::UNKNOWN_START_POINT,
            'destination' => SubmissionResult::UNKNOWN_FINISH_POINT,
            'lineName' => SubmissionResult::UNKNOWN_SERVICE_NO,
            'startDate' => SubmissionResult::UNKNOWN_START_DATE,
            'hasBusData' => false
        ];

        $licNo = 'OB1234567';
        $routeNo = '8910';
        $regNo = $licNo . '/' . $routeNo;
        $startPoint = 'start point';
        $finishPoint = 'end point';

        $serviceNo = 555;
        $otherServiceNos = [666, 777];

        $joinedServiceNo = '555 (666, 777)';

        $effectiveDate = '2016-12-26 00:00:00';
        $effectiveDateTime = new \DateTime($effectiveDate);
        $formattedEffectiveDateTime = $effectiveDateTime->format(SubmissionResult::DATE_FORMAT);

        $populatedInputData = [
            'licNo' => $licNo,
            'routeNo' => $routeNo,
            'serviceNo' => $serviceNo,
            'otherServiceNumbers' => $otherServiceNos,
            'startPoint' => $startPoint,
            'finishPoint' => $finishPoint,
            'effectiveDate' => $effectiveDate
        ];

        $populatedExpectedData = [
            'submissionDate' => $formattedSubmissionDate,
            'registrationNumber' => $regNo,
            'origin' => $startPoint,
            'destination' => $finishPoint,
            'lineName' => $joinedServiceNo,
            'startDate' => $formattedEffectiveDateTime,
            'hasBusData' => true
        ];

        return [
            [$submissionDate, [], $blankExpectedData],
            [$submissionDateTime, $populatedInputData, $populatedExpectedData]
        ];
    }
}
