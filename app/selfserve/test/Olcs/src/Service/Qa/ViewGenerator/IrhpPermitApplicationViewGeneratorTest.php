<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa\ViewGenerator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\ViewGenerator\IrhpPermitApplicationViewGenerator;
use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\RouteMatch;

class IrhpPermitApplicationViewGeneratorTest extends MockeryTestCase
{
    private $irhpPermitApplicationViewGenerator;

    public function setUp(): void
    {
        $this->irhpPermitApplicationViewGenerator = new IrhpPermitApplicationViewGenerator();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'permits/single-question-bilateral',
            $this->irhpPermitApplicationViewGenerator->getTemplateName()
        );
    }

    public function testGetAdditionalViewVariablesPreviousStepPresent(): void
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

    public function testGetAdditionalViewVariablesPreviousStepNotPresent(): void
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

    public function testHandleRedirectionRequestOverview(): void
    {
        $routeParams = ['id' => 100007];

        $response = m::mock(Response::class);

        $redirect = m::mock(Redirect::class);
        $redirect->shouldReceive('getController->params->fromRoute')
            ->withNoArgs()
            ->andReturn($routeParams);
        $redirect->shouldReceive('toRoute')
            ->with(IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW, $routeParams, [])
            ->andReturn($response);

        $this->assertSame(
            $response,
            $this->irhpPermitApplicationViewGenerator->handleRedirectionRequest($redirect, 'OVERVIEW')
        );
    }

    public function testHandleRedirectionRequestCancel(): void
    {
        $id = 100007;
        $slug = 'page-slug';
        $irhpPermitApplication = 2144;

        $routeParams = [
            'id' => $id,
            'slug' => $slug,
            'irhpPermitApplication' => $irhpPermitApplication
        ];

        $expectedRouteParams = [
            'id' => $id
        ];

        $expectedRouteOptions = [
            'query' => [
                'fromBilateralCabotage' => '1',
                'slug' => $slug,
                'ipa' => $irhpPermitApplication
            ]
        ];

        $response = m::mock(Response::class);

        $redirect = m::mock(Redirect::class);
        $redirect->shouldReceive('getController->params->fromRoute')
            ->withNoArgs()
            ->andReturn($routeParams);
        $redirect->shouldReceive('toRoute')
            ->with(IrhpApplicationSection::ROUTE_CANCEL_APPLICATION, $expectedRouteParams, $expectedRouteOptions)
            ->andReturn($response);

        $this->assertSame(
            $response,
            $this->irhpPermitApplicationViewGenerator->handleRedirectionRequest($redirect, 'CANCEL')
        );
    }

    public function testHandleRedirectionRequestException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'IrhpPermitApplicationViewGenerator does not support a destination name of DESTINATION_NAME'
        );

        $redirect = m::mock(Redirect::class);

        $this->irhpPermitApplicationViewGenerator->handleRedirectionRequest($redirect, 'DESTINATION_NAME');
    }
}
