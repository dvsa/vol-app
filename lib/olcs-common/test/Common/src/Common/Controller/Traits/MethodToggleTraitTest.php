<?php

/**
 * Created by PhpStorm.
 * User: parthvyas
 * Date: 05/11/2018
 * Time: 11:19
 */

namespace CommonTest\Common\Controller\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use CommonTest\Common\Controller\Traits\Stubs\MethodToggleTraitStub;

class MethodToggleTraitTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = m::mock(MethodToggleTraitStub::class)
            ->makePartial();
    }

    public function testTogglableMethodWhenToggleOn(): void
    {
        $this->sut->shouldReceive('featuresEnabledForMethod')->andReturn(true);
        $this->sut->togglableMethod($this->sut, 'someMethod');

        $this->assertEquals($this->sut->someMethodString, 'method was called');
    }

    public function testTogglableMethodWhenToggleOff(): void
    {
        $this->sut->shouldReceive('featuresEnabledForMethod')->andReturn(false);
        $this->sut->togglableMethod($this->sut, 'someMethod');

        $this->assertEquals($this->sut->someMethodString, 'method was not called');
    }
}
