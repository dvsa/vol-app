<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\CabotageOnly;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CabotageOnlyTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CabotageOnlyTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetRepresentation')]
    public function testGetRepresentation(mixed $yesNo): void
    {
        $countryName = 'Germany';

        $cabotageOnly = new CabotageOnly($yesNo, $countryName);

        $expectedRepresentation = [
            'yesNo' => $yesNo,
            'countryName' => $countryName,
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $cabotageOnly->getRepresentation()
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
