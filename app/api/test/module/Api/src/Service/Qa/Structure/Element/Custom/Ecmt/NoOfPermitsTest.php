<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\EmissionsCategory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermits;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $maxCanApplyFor = 37;
        $maxPermitted = 42;
        $applicationFee = '15.00';
        $issueFee = '10.00';
        $skipAvailabilityValidation = true;

        $emissionsCategory1Representation = [
            'type' => 'emissionsCategory1Type',
            'value' => 'emissionsCategory1Value',
            'permitsRemaining' => 'emissionsCategory1PermitsRemaining'
        ];

        $emissionsCategory1 = m::mock(EmissionsCategory::class);
        $emissionsCategory1->shouldReceive('getRepresentation')
            ->andReturn($emissionsCategory1Representation);

        $emissionsCategory2Representation = [
            'type' => 'emissionsCategory2Type',
            'value' => 'emissionsCategory2Value',
            'permitsRemaining' => 'emissionsCategory2PermitsRemaining'
        ];

        $emissionsCategory2 = m::mock(EmissionsCategory::class);
        $emissionsCategory2->shouldReceive('getRepresentation')
            ->andReturn($emissionsCategory2Representation);

        $noOfPermits = new NoOfPermits($maxCanApplyFor, $maxPermitted, $applicationFee, $issueFee, $skipAvailabilityValidation);
        $noOfPermits->addEmissionsCategory($emissionsCategory1);
        $noOfPermits->addEmissionsCategory($emissionsCategory2);

        $expectedRepresentation = [
            'maxCanApplyFor' => $maxCanApplyFor,
            'maxPermitted' => $maxPermitted,
            'applicationFee' => $applicationFee,
            'issueFee' => $issueFee,
            'skipAvailabilityValidation' => $skipAvailabilityValidation,
            'emissionsCategories' => [
                $emissionsCategory1Representation,
                $emissionsCategory2Representation
            ]
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $noOfPermits->getRepresentation()
        );
    }
}
