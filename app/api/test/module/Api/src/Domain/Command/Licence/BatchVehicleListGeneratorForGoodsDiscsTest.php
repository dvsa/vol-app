<?php

declare(strict_types=1);

/**
 * BatchVehicleListGeneratorForGoodsDiscs Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Licence;

use Dvsa\Olcs\Api\Domain\Command\Licence\BatchVehicleListGeneratorForGoodsDiscs;

/**
 * BatchVehicleListGeneratorForGoodsDiscs Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BatchVehicleListGeneratorForGoodsDiscsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = BatchVehicleListGeneratorForGoodsDiscs::create(
            [
                'licences' => [1, 2],
                'user' => 1
            ]
        );

        $this->assertEquals([1, 2], $command->getLicences());
        $this->assertEquals(1, $command->getUser());
    }
}
