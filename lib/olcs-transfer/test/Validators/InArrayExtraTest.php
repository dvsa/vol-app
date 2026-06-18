<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\InArrayExtra;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Transfer\Validators\InArrayExtra
 */
class InArrayExtraTest extends MockeryTestCase
{
    /** @var  InArrayExtra */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(InArrayExtra::class)->makePartial();
    }

    public function getSetGet()
    {
        $extHaystack = ['extra_3', 'unit_extra4'];
        $this->sut->setExtraHaystack($extHaystack);
        static::assertSame($extHaystack, $this->sut->getExtraHaystack());
    }

    public function testGetHaystack()
    {
        $this->sut->setHaystack(['opt_1', 'opt_2']);

        $extHaystack = ['extra_3', 'unit_extra4'];
        $this->sut->setExtraHaystack($extHaystack);

        static::assertSame(
            ['opt_1', 'opt_2', 'extra_3', 'unit_extra4'],
            $this->sut->getHaystack()
        );
    }
}
