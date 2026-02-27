<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Irfo;

use Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock
 * @covers Dvsa\Olcs\Api\Entity\Irfo\AbstractIrfoPermitStock
 */
class IrfoPermitStockEntityTest extends EntityTester
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

        static::assertEquals($serialNo, $sut->getSerialNo());
        static::assertEquals($validForYear, $sut->getValidForYear());
        static::assertEquals($ifroCntr, $sut->getIrfoCountry());
    }
}
