<?php

declare(strict_types=1);

/**
 * CreatePsvVehicleListForDiscs Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Discs;

use Dvsa\Olcs\Api\Domain\Command\Discs\CreatePsvVehicleListForDiscs;

/**
 * CreatePsvVehicleListForDiscs Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreatePsvVehicleListForDiscsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = CreatePsvVehicleListForDiscs::create(
            [
                'knownValues' => ['d1', 'd2'],
                'id' => 1,
                'user' => 1
            ]
        );

        $this->assertEquals(['d1', 'd2'], $command->getKnownValues());
        $this->assertEquals(1, $command->getId());
        $this->assertEquals(1, $command->getUser());
    }
}
