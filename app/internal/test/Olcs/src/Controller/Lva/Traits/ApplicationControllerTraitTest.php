<?php

/**
 * Application Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\VariationCompletionEntityService;

/**
 * Application Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTraitTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->markTestSkipped();
        // @NOTE Appologies for mocking the SUT, but these tests are more than adequate to UNIT test the
        // behaviour of the work done in my story, any other tests would have been expenasive
        $this->sut = m::mock('\OlcsTest\Controller\Lva\Traits\Stubs\ApplicationControllerTraitStub')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
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

    public function testGetSectionsForViewHiddenWhenValid()
    {
        // Params
        $id = 3;
        $mockStatuses = [
            'type_of_licence' => VariationCompletionEntityService::STATUS_UPDATED,
            'business_type' => VariationCompletionEntityService::STATUS_UNCHANGED,
            'business_details' => VariationCompletionEntityService::STATUS_REQUIRES_ATTENTION,
        ];

        $expected = [
            'overview' => [
                'class' => 'no-background',
                'route' => 'lva-application',
                'enabled' => true,
            ],
        ];

        // Setup

        // Mocks
        $mockApplicationCompletion = m::mock();
        $this->sm->setService('Entity\ApplicationCompletion', $mockApplicationCompletion);

        $mockApplicationEntityService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationEntityService);

        // Expectations
        $this->sut->shouldReceive('getApplicationId')->with()->twice()->andReturn($id);

        $mockApplicationCompletion->shouldReceive('getCompletionStatuses')
            ->with($id)
            ->andReturn($mockStatuses);

        $mockApplicationEntityService->shouldReceive('getStatus')->with($id)->once()->andReturn('apsts_valid');

        $response = $this->sut->callGetSectionsForView();

        $this->assertEquals($expected, $response);
    }
}
