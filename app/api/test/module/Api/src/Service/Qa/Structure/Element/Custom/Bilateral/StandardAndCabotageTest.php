<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotage;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $value = 'standard_and_cabotage_value';

        $cabotageOnly = new StandardAndCabotage($value);

        $expectedRepresentation = [
            'value' => $value
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $cabotageOnly->getRepresentation()
        );
    }
}
