<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\EmissionsStandards;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsStandardsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsStandardsTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetRepresentation')]
    public function testGetRepresentation(mixed $yesNo): void
    {
        $emissionsStandards = new EmissionsStandards($yesNo);

        $expectedRepresentation = ['yesNo' => $yesNo];

        $this->assertEquals(
            $expectedRepresentation,
            $emissionsStandards->getRepresentation()
        );
    }

    public static function dpGetRepresentation(): array
    {
        return [
            ['Y'],
            [null]
        ];
    }
}
