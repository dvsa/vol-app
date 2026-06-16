<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessFee;
use Dvsa\Olcs\Transfer\Query\Fee\Fee as FeeQry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessFee
 */
class CanAccessFeeTest extends MockeryTestCase
{
    /**
     * @var CanAccessFee|m\Mock
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(CanAccessFee::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function testIsValidWithNoLicenceOrApplicationContext(): void
    {
        $dto = FeeQry::create(['id' => 111]);

        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('getDto')->with()->once()->andReturn($dto);

        $this->assertTrue($this->sut->isValid(111));
    }

    public function testIsValidWhenFeeBelongsToLicence(): void
    {
        $dto = FeeQry::create(['id' => 111, 'licenceId' => 123]);

        $licence = m::mock();
        $licence->shouldReceive('getId')->andReturn(123);

        $fee = m::mock();
        $fee->shouldReceive('getLicence')->andReturn($licence);

        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('getDto')->with()->once()->andReturn($dto);
        $this->sut->shouldReceive('getEntity')->with(111)->once()->andReturn($fee);

        $this->assertTrue($this->sut->isValid(111));
    }

    public function testIsNotValidWhenFeeDoesNotBelongToLicence(): void
    {
        $dto = FeeQry::create(['id' => 111, 'licenceId' => 123]);

        $licence = m::mock();
        $licence->shouldReceive('getId')->andReturn(999);

        $fee = m::mock();
        $fee->shouldReceive('getLicence')->andReturn($licence);

        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('getDto')->with()->once()->andReturn($dto);
        $this->sut->shouldReceive('getEntity')->with(111)->once()->andReturn($fee);

        $this->assertFalse($this->sut->isValid(111));
    }

    public function testIsValidWhenFeeBelongsToApplication(): void
    {
        $dto = FeeQry::create(['id' => 111, 'applicationId' => 456]);

        $application = m::mock();
        $application->shouldReceive('getId')->andReturn(456);

        $fee = m::mock();
        $fee->shouldReceive('getApplication')->andReturn($application);

        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('getDto')->with()->once()->andReturn($dto);
        $this->sut->shouldReceive('getEntity')->with(111)->once()->andReturn($fee);

        $this->assertTrue($this->sut->isValid(111));
    }

    public function testIsNotValidWhenFeeDoesNotBelongToApplication(): void
    {
        $dto = FeeQry::create(['id' => 111, 'applicationId' => 456]);

        $application = m::mock();
        $application->shouldReceive('getId')->andReturn(999);

        $fee = m::mock();
        $fee->shouldReceive('getApplication')->andReturn($application);

        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('getDto')->with()->once()->andReturn($dto);
        $this->sut->shouldReceive('getEntity')->with(111)->once()->andReturn($fee);

        $this->assertFalse($this->sut->isValid(111));
    }

    public function testIsNotValidWhenFeeHasNoLicence(): void
    {
        $dto = FeeQry::create(['id' => 111, 'licenceId' => 123]);

        $fee = m::mock();
        $fee->shouldReceive('getLicence')->andReturn(null);

        $this->sut->shouldReceive('isInternalUser')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('getDto')->with()->once()->andReturn($dto);
        $this->sut->shouldReceive('getEntity')->with(111)->once()->andReturn($fee);

        $this->assertFalse($this->sut->isValid(111));
    }
}
