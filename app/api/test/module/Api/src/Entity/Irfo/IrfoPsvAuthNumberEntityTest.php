<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Irfo;

use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthNumber as Entity;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthNumber
 * @covers Dvsa\Olcs\Api\Entity\Irfo\AbstractIrfoPsvAuthNumber
 */
class IrfoPsvAuthNumberEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  IrfoPsvAuth */
    private $mockIrfoPsvAuth;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockIrfoPsvAuth = m::mock(IrfoPsvAuth::class);
    }

    public function testConstructor(): void
    {
        $name = 'unit_Name';

        $sut = new Entity($this->mockIrfoPsvAuth, $name);

        static::assertEquals($this->mockIrfoPsvAuth, $sut->getIrfoPsvAuth());
        static::assertEquals($name, $sut->getName());
    }

    public function testGetCalculatedValues(): void
    {
        $actual = (new Entity($this->mockIrfoPsvAuth, ''))->jsonSerialize();
        static::assertNull($actual['irfoPsvAuth']);
    }
}
