<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\InArrayExtra;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Validators\InArrayExtra::class)]
final class InArrayExtraTest extends MockeryTestCase
{
    /** @var  InArrayExtra */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = m::mock(InArrayExtra::class)->makePartial();
    }

    public function getSetGet()
    {
        $extHaystack = ['extra_3', 'unit_extra4'];
        $this->sut->setExtraHaystack($extHaystack);
        $this->assertSame($extHaystack, $this->sut->getExtraHaystack());
    }

    public function testGetHaystack()
    {
        $this->sut->setHaystack(['opt_1', 'opt_2']);

        $extHaystack = ['extra_3', 'unit_extra4'];
        $this->sut->setExtraHaystack($extHaystack);

        $this->assertSame(['opt_1', 'opt_2', 'extra_3', 'unit_extra4'], $this->sut->getHaystack());
    }
}
