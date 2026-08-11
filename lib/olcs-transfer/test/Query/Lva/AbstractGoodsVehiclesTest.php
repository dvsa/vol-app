<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Lva;

use Dvsa\Olcs\Transfer\Query\Lva\AbstractGoodsVehicles;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Lva\AbstractGoodsVehicles::class)]
final class AbstractGoodsVehiclesTest extends \PHPUnit\Framework\TestCase
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

        $this->assertEquals('unit_Vrm', $sut->getVrm());
        $this->assertEquals('unit_Spec', $sut->getSpecified());
        $this->assertEquals('unit_IncRem', $sut->getIncludeRemoved());
        $this->assertEquals('unit_Disc', $sut->getDisc());
    }
}
