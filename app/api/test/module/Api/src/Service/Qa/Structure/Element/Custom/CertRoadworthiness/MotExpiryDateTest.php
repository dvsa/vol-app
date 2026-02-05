<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness\MotExpiryDate;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThreshold;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * MotExpiryDateTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MotExpiryDateTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTrueFalse')]
    public function testGetRepresentation(mixed $enableFileUploads): void
    {
        $dateWithThresholdRepresentation = [
            'dateWithThresholdKey1' => 'dateWithThresholdValue1',
            'dateWithThresholdKey2' => 'dateWithThresholdValue2',
        ];

        $dateWithThreshold = m::mock(DateWithThreshold::class);
        $dateWithThreshold->shouldReceive('getRepresentation')
            ->andReturn($dateWithThresholdRepresentation);

        $motExpiryDate = new MotExpiryDate($enableFileUploads, $dateWithThreshold);

        $expectedRepresentation = [
            'enableFileUploads' => $enableFileUploads,
            'dateWithThreshold' => $dateWithThresholdRepresentation,
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $motExpiryDate->getRepresentation()
        );
    }

    public static function dpTrueFalse(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
