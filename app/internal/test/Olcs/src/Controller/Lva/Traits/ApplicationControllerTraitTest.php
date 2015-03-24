<?php

/**
 * Application Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Entity\ApplicationEntityService;

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

    /**
     * @dataProvider colourStatusProvider
     * @param string $status
     * @param string $expectedColour
     */
    public function testGetColourForStatus($status, $expectedColour)
    {
        $this->assertEquals($expectedColour, $this->sut->getColourForStatus($status));
    }

    public function colourStatusProvider()
    {
        return [
            [ApplicationEntityService::APPLICATION_STATUS_NOT_SUBMITTED, 'grey'],
            [ApplicationEntityService::APPLICATION_STATUS_GRANTED, 'orange'],
            [ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION, 'orange'],
            [ApplicationEntityService::APPLICATION_STATUS_VALID, 'green'],
            [ApplicationEntityService::APPLICATION_STATUS_WITHDRAWN, 'red'],
            [ApplicationEntityService::APPLICATION_STATUS_REFUSED, 'red'],
            ['somethingelse', 'grey'],
        ];
    }
}
