<?php

namespace OlcsTest\Controller\Lva\Variation;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PeopleControllerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PeopleControllerTest extends MockeryTestCase
{
    protected $sut;

    protected function setUp()
    {
        $this->sut = m::mock(\Olcs\Controller\Lva\Variation\PeopleController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testDisqualifyAction()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('application')->once()->andReturn(128);
        $this->sut->shouldReceive('params->fromRoute')->with('child_id')->once()->andReturn(935);
        $this->sut->shouldReceive('forward->dispatch')->with(
            \Olcs\Controller\DisqualifyController::class,
            [
                'action' => 'index',
                'variation' => 128,
                'person' => 935,
            ]
        )->once()->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->disqualifyAction());
    }
}
