<?php

/**
 * Application Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Application Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTraitTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        // @NOTE Appologies for mocking the SUT, but these tests are more than adequate to UNIT test the
        // behaviour of the work done in my story, any other tests would have been expenasive
        $this->sut = m::mock('\OlcsTest\Controller\Lva\Traits\Stubs\ApplicationControllerTraitStub')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testPreDispatch()
    {
        $this->sut->shouldReceive('getApplicationId')
            ->andReturn(123)
            ->shouldReceive('isApplicationNew')
            ->with(123)
            ->andReturn(true)
            ->shouldReceive('checkForRedirect')
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->callPreDispatch());
    }

    public function testPreDispatchWithRedirect()
    {
        $this->sut->shouldReceive('getApplicationId')
            ->andReturn(123)
            ->shouldReceive('isApplicationNew')
            ->with(123)
            ->andReturn(false);

        $this->sut->shouldReceive('getEvent->getRouteMatch->getMatchedRouteName')
            ->andReturn('lva-application/foo/bar');

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('lva-variation/foo/bar', [], [], true)
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->callPreDispatch());
    }
}
