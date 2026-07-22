<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Irfo;

use Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Irfo\AbstractIrfoPermitStock::class)]
final class IrfoPermitStockEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstructor(): void
    {
        $serialNo = 'unit_SerialNo';
        $validForYear = 'unit_ValidForYear';
        $ifroCntr = new IrfoCountry();

        $sut = new Entity($serialNo, $validForYear, $ifroCntr);

        $this->assertEquals($serialNo, $sut->getSerialNo());
        $this->assertEquals($validForYear, $sut->getValidForYear());
        $this->assertEquals($ifroCntr, $sut->getIrfoCountry());
    }
}
