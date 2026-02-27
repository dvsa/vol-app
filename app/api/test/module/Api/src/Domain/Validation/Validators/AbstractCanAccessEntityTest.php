<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\AbstractCanAccessEntity;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Validators\AbstractCanAccessEntity
 */
class AbstractCanAccessEntityTest extends MockeryTestCase
{
    /**
     * @var AbstractCanAccessEntity|m\Mock
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(AbstractCanAccessEntity::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function testIsValidInternal(): void
    {
        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(true);

        $this->assertTrue($this->sut->isValid(111));
    }

    public function testIsValidSystem(): void
    {
        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(false);
        $this->sut->shouldReceive('isSystemUser')->with()->once()->andReturn(true);

        $this->assertTrue($this->sut->isValid(111));
    }

    public function testIsValidEntity(): void
    {
        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(false);
        $this->sut->shouldReceive('isSystemUser')->with()->once()->andReturn(false);
        $this->sut->shouldReceive('getEntity')->with(111)->once()->andReturn('E');
        $this->sut->shouldReceive('isOwner')->with('E')->once()->andReturn(true);

        $this->assertTrue($this->sut->isValid(111));
    }
}
