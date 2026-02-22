<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Irfo;

use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPartner as Entity;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Irfo\IrfoPartner
 * @covers Dvsa\Olcs\Api\Entity\Irfo\AbstractIrfoPartner
 */
class IrfoPartnerEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  Organisation */
    private $mockOrg;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockOrg = m::mock(Organisation::class);
    }

    public function testConstructor(): void
    {
        $name = 'unit_Name';

        $sut = new Entity($this->mockOrg, $name);

        static::assertEquals($this->mockOrg, $sut->getOrganisation());
        static::assertEquals($name, $sut->getName());
    }

    public function testGetCalculatedValues(): void
    {
        $actual = (new Entity($this->mockOrg, ''))->jsonSerialize();
        static::assertNull($actual['organisation']);
    }
}
