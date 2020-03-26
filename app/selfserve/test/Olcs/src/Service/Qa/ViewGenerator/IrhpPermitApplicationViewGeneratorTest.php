<?php

namespace OlcsTest\Service\Qa\ViewGenerator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\ViewGenerator\IrhpPermitApplicationViewGenerator;
use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;
use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;

class IrhpPermitApplicationViewGeneratorTest extends MockeryTestCase
{
    private $irhpPermitApplicationViewGenerator;

    public function setUp()
    {
        $this->irhpPermitApplicationViewGenerator = new IrhpPermitApplicationViewGenerator();
    }

    public function testGetTemplateName()
    {
        $this->assertEquals(
            'permits/single-question-bilateral',
            $this->irhpPermitApplicationViewGenerator->getTemplateName()
        );
    }

    public function testGetFormName()
    {
        $this->assertEquals(
            'QaBilateralForm',
            $this->irhpPermitApplicationViewGenerator->getFormName()
        );
    }

    public function testGetAdditionalViewVariablesPreviousStepPresent()
    {
        $matchedRouteName = 'ipaQuestion';
        $countryName = 'Germany';
        $previousStepSlug = 'previous-step-slug';

        $result = [
            'additionalViewData' => [
                'countryName' => $countryName,
                'previousStepSlug' => $previousStepSlug
            ]
        ];

        $currentUriParams = [
            'id' => 100007,
            'slug' => 'currentSlug'
        ];

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->shouldReceive('getParams')
            ->withNoArgs()
            ->andReturn($currentUriParams);
        $routeMatch->shouldReceive('getMatchedRouteName')
            ->withNoArgs()
            ->andReturn($matchedRouteName);

        $mvcEvent = m::mock(MvcEvent::class);
        $mvcEvent->shouldReceive('getRouteMatch')
            ->withNoArgs()
            ->andReturn($routeMatch);

        $expected = [
            'backUri' => $matchedRouteName,
            'backUriParams' => [
                'id' => 100007,
                'slug' => $previousStepSlug
            ],
            'cancelUrl' => IrhpApplicationSection::ROUTE_PERMITS,
            'application' => [
                'countryName' => $countryName
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->irhpPermitApplicationViewGenerator->getAdditionalViewVariables($mvcEvent, $result)
        );
    }

    public function testGetAdditionalViewVariablesPreviousStepNotPresent()
    {
        $countryName = 'Germany';
        $countryCode = 'DE';

        $result = [
            'additionalViewData' => [
                'countryName' => $countryName,
                'previousStepSlug' => null,
                'countryCode' => $countryCode
            ]
        ];

        $currentUriParams = ['id' => 100007];

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->shouldReceive('getParams')
            ->withNoArgs()
            ->andReturn($currentUriParams);

        $mvcEvent = m::mock(MvcEvent::class);
        $mvcEvent->shouldReceive('getRouteMatch')
            ->withNoArgs()
            ->andReturn($routeMatch);

        $expected = [
            'backUri' => IrhpApplicationSection::ROUTE_PERIOD,
            'backUriParams' => [
                'id' => 100007,
                'country' => $countryCode
            ],
            'cancelUrl' => IrhpApplicationSection::ROUTE_PERMITS,
            'application' => [
                'countryName' => $countryName
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->irhpPermitApplicationViewGenerator->getAdditionalViewVariables($mvcEvent, $result)
        );
    }

    /**
     * @dataProvider dpHandleRedirectionRequest
     */
    public function testHandleRedirectionRequest($destinationName, $routeName)
    {
        $routeParams = ['id' => 100007];

        $response = m::mock(Response::class);

        $redirect = m::mock(Redirect::class);
        $redirect->shouldReceive('getController->params->fromRoute')
            ->withNoArgs()
            ->andReturn($routeParams);
        $redirect->shouldReceive('toRoute')
            ->with($routeName, $routeParams)
            ->andReturn($response);

        $this->assertSame(
            $response,
            $this->irhpPermitApplicationViewGenerator->handleRedirectionRequest($redirect, $destinationName)
        );
    }

    public function dpHandleRedirectionRequest()
    {
        return [
            ['OVERVIEW', IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW],
            ['CANCEL', IrhpApplicationSection::ROUTE_CANCEL_APPLICATION],
        ];
    }

    public function testHandleRedirectionRequestException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'IrhpPermitApplicationViewGenerator does not support a destination name of DESTINATION_NAME'
        );

        $redirect = m::mock(Redirect::class);

        $this->irhpPermitApplicationViewGenerator->handleRedirectionRequest($redirect, 'DESTINATION_NAME');
    }
}
