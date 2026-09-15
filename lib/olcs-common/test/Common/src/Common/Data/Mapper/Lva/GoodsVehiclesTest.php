<?php

/**
 * Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\GoodsVehicles;

/**
 * Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GoodsVehiclesTest extends \PHPUnit\Framework\TestCase
{
    public function testMapFromResult(): void
    {
        $input = [
            'version' => 1,
            'hasEnteredReg' => 'N'
        ];

        $output = GoodsVehicles::mapFromResult($input);

        $expected = [
            'data' => [
                'version' => 1,
                'hasEnteredReg' => 'N'
            ]
        ];

        $this->assertEquals($expected, $output);
    }

    public function testMapFromResult2(): void
    {
        $input = [
            'version' => 1,
            'hasEnteredReg' => 'Y'
        ];

        $output = GoodsVehicles::mapFromResult($input);

        $expected = [
            'data' => [
                'version' => 1,
                'hasEnteredReg' => 'Y'
            ]
        ];

        $this->assertEquals($expected, $output);
    }
}
