<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Mvc;

use Dvsa\Olcs\Api\Mvc\PayloadValidationListener as Sut;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as TestQuery;
use Dvsa\Olcs\Api\Domain\Command\MyAccount\UpdateMyAccount as TestCommand;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;

class PayloadValidationListenerTest extends MockeryTestCase
{
    public $annotationBuilder;
    public $sut;
    public function setUp(): void
    {
        $this->annotationBuilder = m::mock(AnnotationBuilder::class);

        $this->sut = new Sut($this->annotationBuilder);
        parent::setUp();
    }

    public function testAttach(): void
    {
        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')
            ->once()
            ->with(MvcEvent::EVENT_ROUTE, m::type(\Closure::class), 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnRouteNoRequest(): void
    {
        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn(null);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertNull($result);
    }

    public function testOnRouteNoRouteMatch(): void
    {
        $mockHttpRequest = m::mock(HttpRequest::class);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn(null);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertNull($result);
    }

    public function testOnRouteNoDtoClass(): void
    {
        $mockHttpRequest = m::mock(HttpRequest::class);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(null);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertNull($result);
    }

    public function testOnRouteGetValid(): void
    {
        $params = [];

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->expects('isGet')->withNoArgs()->andReturnTrue();
        $mockHttpRequest->expects('getQuery')->withNoArgs()->andReturn([]);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestQuery::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestQuery::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->annotationBuilder->shouldReceive('createQuery')
            ->once()
            ->with(m::type(TestQuery::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertNull($result);
    }

    public function testOnRouteGetNotValid(): void
    {
        $params = [];

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->expects('isGet')->withNoArgs()->andReturnTrue();
        $mockHttpRequest->expects('getQuery')->withNoArgs()->andReturn([]);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestQuery::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestQuery::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['ERROR']);

        $this->annotationBuilder->shouldReceive('createQuery')
            ->once()
            ->with(m::type(TestQuery::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(HttpResponse::STATUS_CODE_422, $result->getStatusCode());
        $this->assertEquals(['Content-Type' => 'application/json'], $result->getHeaders()->toArray());
        $this->assertEquals('["ERROR"]', $result->getContent());
    }

    public function testOnRoutePostJsonNotValid(): void
    {
        $params = [];
        $data = ['POST_DATA'];

        $mockContentType = m::mock();
        $mockContentType->shouldReceive('getMediaType')
            ->once()
            ->andReturn(Sut::JSON_MEDIA_TYPE);

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->expects('isGet')->withNoArgs()->andReturnFalse();
        $mockHttpRequest->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn($mockContentType)
            ->shouldReceive('getContent')
            ->once()
            ->andReturn(json_encode($data));

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestCommand::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestCommand::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(CommandInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['ERROR']);

        $this->annotationBuilder->shouldReceive('createCommand')
            ->once()
            ->with(m::type(TestCommand::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(Sut::JSON_NOT_VALID_CODE, $result->getStatusCode());
        $this->assertEquals(['Content-Type' => 'application/json'], $result->getHeaders()->toArray());
        $this->assertEquals('["ERROR"]', $result->getContent());
    }

    public function testOnRoutePostXmlNotValid(): void
    {
        $params = [];
        $data = '<xml><some_data>POST_DATA</some_data></xml>';

        $mockContentType = m::mock();
        $mockContentType->shouldReceive('getMediaType')
            ->once()
            ->andReturn('text/xml');

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->expects('isGet')->withNoArgs()->andReturnFalse();
        $mockHttpRequest->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn($mockContentType)
            ->shouldReceive('getContent')
            ->once()
            ->andReturn($data);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestCommand::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestCommand::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(CommandInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $this->annotationBuilder->shouldReceive('createCommand')
            ->once()
            ->with(m::type(TestCommand::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(Sut::XML_NOT_VALID_CODE, $result->getStatusCode());
        $this->assertEquals([], $result->getHeaders()->toArray());
        $this->assertEquals('', $result->getContent());
    }

    public function testOnRoutePostDataNotValid(): void
    {
        $params = [];
        $data = ['data'];
        $files = ['field' => 'file'];

        $mockContentType = m::mock();
        $mockContentType->shouldReceive('getMediaType')
            ->once()
            ->andReturn('application/x-www-form-urlencoded');

        $mockHttpRequest = m::mock(HttpRequest::class);
        $mockHttpRequest->expects('isGet')->withNoArgs()->andReturnFalse();
        $mockHttpRequest->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn($mockContentType)
            ->shouldReceive('getPost')
            ->once()
            ->andReturn($data)
            ->shouldReceive('getFiles')
            ->once()
            ->andReturn($files);

        $mockRouteMatch = m::mock(RouteMatch::class);
        $mockRouteMatch->shouldReceive('getParam')
            ->with('dto', false)
            ->once()
            ->andReturn(TestCommand::class)
            ->shouldReceive('getParams')
            ->once()
            ->andReturn($params)
            ->shouldReceive('setParam')
            ->once()
            ->with('dto', m::type(TestCommand::class))
            ->andReturn($params);

        $mockMvcEvent = m::mock(MvcEvent::class);
        $mockMvcEvent->shouldReceive('getRequest')
            ->once()
            ->andReturn($mockHttpRequest)
            ->shouldReceive('getRouteMatch')
            ->once()
            ->andReturn($mockRouteMatch);

        $mockQuery = m::mock(CommandInterface::class);
        $mockQuery->shouldReceive('isValid')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['ERROR']);

        $this->annotationBuilder->shouldReceive('createCommand')
            ->once()
            ->with(m::type(TestCommand::class))
            ->andReturn($mockQuery);

        $result = $this->sut->onRoute($mockMvcEvent);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(Sut::JSON_NOT_VALID_CODE, $result->getStatusCode());
        $this->assertEquals(['Content-Type' => 'application/json'], $result->getHeaders()->toArray());
        $this->assertEquals('["ERROR"]', $result->getContent());
    }
}
