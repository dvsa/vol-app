<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\AbstractCanEditEntity;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Validators\AbstractCanEditEntity
 */
class AbstractCanEditEntityTest extends MockeryTestCase
{
    /**
     * @var AbstractCanEditEntity|m\Mock
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(AbstractCanEditEntity::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function testIsValidInternalEdit()
    {
        $this->sut->shouldReceive('isGranted')->with(Permission::INTERNAL_EDIT)->once()->andReturn(true);

        $this->assertTrue($this->sut->isValid(111));
    }

    public function testIsValidSystem()
    {
        $this->sut->shouldReceive('isGranted')->with(Permission::INTERNAL_EDIT)->once()->andReturn(false);
        $this->sut->shouldReceive('isSystemUser')->with()->once()->andReturn(true);

        $this->assertTrue($this->sut->isValid(111));
    }

    public function testIsValidEntity()
    {
        $this->sut->shouldReceive('isGranted')->with(Permission::INTERNAL_EDIT)->once()->andReturn(false);
        $this->sut->shouldReceive('isSystemUser')->with()->once()->andReturn(false);
        $this->sut->shouldReceive('getEntity')->with(111)->once()->andReturn('E');
        $this->sut->shouldReceive('isOwner')->with('E')->once()->andReturn(true);

        $this->assertTrue($this->sut->isValid(111));
    }
}
