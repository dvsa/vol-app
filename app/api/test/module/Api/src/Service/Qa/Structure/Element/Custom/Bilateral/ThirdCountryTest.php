<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ThirdCountry;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ThirdCountryTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ThirdCountryTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetRepresentation')]
    public function testGetRepresentation(mixed $yesNo): void
    {
        $thirdCountry = new ThirdCountry($yesNo);

        $expectedRepresentation = ['yesNo' => $yesNo];

        $this->assertEquals(
            $expectedRepresentation,
            $thirdCountry->getRepresentation()
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
