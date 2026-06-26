<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Traits;

use Common\Controller\Traits\PostRedirectGetTrait;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\Session\Container;
use Laminas\Stdlib\Parameters;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PostRedirectGetTraitTest extends MockeryTestCase
{
    private Container $container;

    /**
     * @var object
     */
    private $sut;

    private Request $request;

    private m\MockInterface $redirect;

    private m\MockInterface $params;

    private m\MockInterface $mvcEvent;

    private RouteMatch $routeMatch;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = new Container('test_prg');
        $this->container->offsetUnset('post');

        $this->request = new Request();
        $this->request->setUri('https://example.test/some/path');

        $this->redirect = m::mock(Redirect::class);
        $this->params = m::mock(Params::class);
        $this->routeMatch = new RouteMatch([]);
        $this->routeMatch->setMatchedRouteName('some/route');

        $this->mvcEvent = m::mock(MvcEvent::class);

        $this->sut = new class ($this->request, $this->redirect, $this->params, $this->mvcEvent) {
            use PostRedirectGetTrait;

            public function __construct(
                private Request $request,
                private $redirect,
                private $params,
                private MvcEvent $event,
            ) {
            }

            public function getRequest(): Request
            {
                return $this->request;
            }

            public function redirect()
            {
                return $this->redirect;
            }

            public function params()
            {
                return $this->params;
            }

            public function getEvent(): MvcEvent
            {
                return $this->event;
            }
        };

        $this->sut->setPrgSessionContainer($this->container);
    }

    public function testPostStoresDataAndReturns303Redirect(): void
    {
        $this->request->setMethod(Request::METHOD_POST);
        $this->request->setPost(new Parameters(['field' => 'value']));

        $this->mvcEvent->expects('getRouteMatch')->withNoArgs()->andReturn($this->routeMatch);
        $this->params->expects('fromQuery')->withNoArgs()->andReturn(['q' => 'x']);

        $response = new Response();
        $this->redirect
            ->expects('toRoute')
            ->with('some/route', [], ['query' => ['q' => 'x']], true)
            ->andReturn($response);

        $result = $this->sut->prg();

        $this->assertSame($response, $result);
        $this->assertSame(303, $result->getStatusCode());
        $this->assertEquals(['field' => 'value'], $this->container->offsetGet('post'));
    }

    public function testGetWithStoredDataReturnsArrayAndClearsContainer(): void
    {
        $this->container->offsetSet('post', ['field' => 'value']);
        $this->request->setMethod(Request::METHOD_GET);

        $result = $this->sut->prg();

        $this->assertEquals(['field' => 'value'], $result);
        $this->assertFalse($this->container->offsetExists('post'));
    }

    public function testGetWithoutStoredDataReturnsFalse(): void
    {
        $this->request->setMethod(Request::METHOD_GET);

        $this->assertFalse($this->sut->prg());
    }

    public function testFullPostRedirectGetRoundTrip(): void
    {
        // POST stores data and redirects
        $this->request->setMethod(Request::METHOD_POST);
        $this->request->setPost(new Parameters(['name' => 'alice']));
        $this->mvcEvent->expects('getRouteMatch')->withNoArgs()->andReturn($this->routeMatch);
        $this->params->expects('fromQuery')->withNoArgs()->andReturn([]);
        $this->redirect
            ->expects('toRoute')
            ->with('some/route', [], ['query' => []], true)
            ->andReturn(new Response());

        $postResult = $this->sut->prg();

        $this->assertInstanceOf(Response::class, $postResult);
        $this->assertEquals(['name' => 'alice'], $this->container->offsetGet('post'));

        // GET replay returns the stored data
        $this->request->setMethod(Request::METHOD_GET);
        $this->request->setPost(new Parameters([]));

        $getResult = $this->sut->prg();

        $this->assertEquals(['name' => 'alice'], $getResult);
        $this->assertFalse($this->container->offsetExists('post'));
    }
}
