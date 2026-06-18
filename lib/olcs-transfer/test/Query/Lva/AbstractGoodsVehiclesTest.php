<?php

namespace Dvsa\OlcsTest\Transfer\Query\Lva;

use Dvsa\Olcs\Transfer\Query\Lva\AbstractGoodsVehicles;

/**
 * @covers Dvsa\Olcs\Transfer\Query\Lva\AbstractGoodsVehicles
 */
class AbstractGoodsVehiclesTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet()
    {
        $class = new class extends AbstractGoodsVehicles {
        };

        $data = [
            'vrm' => 'unit_Vrm',
            'specified' => 'unit_Spec',
            'includeRemoved' => 'unit_IncRem',
            'disc' => 'unit_Disc',
        ];
        $sut = $class::create($data);

        static::assertEquals($sut->getVrm(), 'unit_Vrm');
        static::assertEquals($sut->getSpecified(), 'unit_Spec');
        static::assertEquals($sut->getIncludeRemoved(), 'unit_IncRem');
        static::assertEquals($sut->getDisc(), 'unit_Disc');
    }
}
