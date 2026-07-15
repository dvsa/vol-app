<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\FeeBelongsToBusReg;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Mockery as m;

/**
 * Fee Belongs To Bus Reg Test
 */
final class FeeBelongsToBusRegTest extends AbstractValidatorsTestCase
{
    /**
     * @var FeeBelongsToBusReg
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new FeeBelongsToBusReg();

        parent::setUp();
    }

    public function testIsValidMatchingId(): void
    {
        $busReg = m::mock(BusReg::class);
        $busReg->expects('getId')->withNoArgs()->andReturn(42);

        $fee = m::mock(Fee::class);
        $fee->expects('getBusReg')->withNoArgs()->andReturn($busReg);

        $this->assertTrue($this->sut->isValid($fee, 42));
    }

    public function testIsValidMismatchedId(): void
    {
        $busReg = m::mock(BusReg::class);
        $busReg->expects('getId')->withNoArgs()->andReturn(42);

        $fee = m::mock(Fee::class);
        $fee->expects('getBusReg')->withNoArgs()->andReturn($busReg);

        $this->assertFalse($this->sut->isValid($fee, 999));
    }

    public function testIsValidEntityToEntity(): void
    {
        $busReg = m::mock(BusReg::class);
        $busReg->expects('getId')->withNoArgs()->andReturn(42);

        $parent = m::mock(BusReg::class);
        $parent->expects('getId')->withNoArgs()->andReturn(42);

        $fee = m::mock(Fee::class);
        $fee->expects('getBusReg')->withNoArgs()->andReturn($busReg);

        $this->assertTrue($this->sut->isValid($fee, $parent));
    }

    public function testIsValidFeeHasNoBusReg(): void
    {
        $fee = m::mock(Fee::class);
        $fee->expects('getBusReg')->withNoArgs()->andReturnNull();

        $this->assertFalse($this->sut->isValid($fee, 42));
    }
}
