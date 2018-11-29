<?php

/**
 * Variation Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Query\Application\Application as VariationQry;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\VariationFurniture;
use Mockery as m;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ViewModel;

/**
 * Variation Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationFurnitureTest extends TestCase
{
    /**
     * @var VariationFurniture
     */
    protected $sut;

    protected $mockViewHelperManager;
    protected $mockQuerySender;
    protected $mockRouter;

    public function setUp()
    {
        $this->mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->mockQuerySender = m::mock(QuerySender::class);
        $this->mockRouter = m::mock(RouteStackInterface::class);

        $this->sut = new VariationFurniture();

        $sl = m::mock(ServiceLocatorInterface::class);

        $sl->shouldReceive('get')->with('ViewHelperManager')->andReturn($this->mockViewHelperManager);
        $sl->shouldReceive('get')->with('QuerySender')->andReturn($this->mockQuerySender);
        $sl->shouldReceive('get')->with('Router')->andReturn($this->mockRouter);

        $this->sut->createService($sl);
    }

    public function testAttach()
    {
        $events = m::mock(EventManagerInterface::class);

        $events->shouldReceive('attach')->once()
            ->with('route.param.application', [$this->sut, 'onVariationFurniture'], 1)
            ->andReturn('listener');

        $this->sut->attach($events);
    }

    public function testOnVariationFurnitureWithError()
    {
        $this->expectException(ResourceNotFoundException::class);

        $event = m::mock(RouteParam::class);
        $event->shouldReceive('getValue')->andReturn(111);

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(false);

        $this->mockQuerySender->shouldReceive('send')->once()
            ->with(m::type(VariationQry::class))
            ->andReturn($response);

        $this->sut->onVariationFurniture($event);
    }

    public function testOnVariationFurniture()
    {
        $event = m::mock(RouteParam::class);
        $event->shouldReceive('getValue')->andReturn(111);

        $status = [
            'id' => RefData::APPLICATION_STATUS_VALID
        ];

        $mockPlaceholder = m::mock();
        $mockPlaceholder->shouldReceive('getContainer')
            ->with('pageTitle')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->once()
                ->with('<a href="url">AB123</a> / 111')
                ->getMock()
            )
            ->shouldReceive('getContainer')
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->once()
                ->with('Foo ltd')
                ->getMock()
            )
            ->shouldReceive('getContainer')
            ->with('status')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($status)
                    ->getMock()
            )
            ->shouldReceive('getContainer')
            ->with('horizontalNavigationId')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('application')
                    ->getMock()
            )
            ->shouldReceive('getContainer')
            ->with('right')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with(m::type(ViewModel::class))
                    ->getMock()
            );

        $this->mockViewHelperManager->shouldReceive('get')
            ->with('placeholder')->andReturn($mockPlaceholder);

        $this->mockRouter->shouldReceive('assemble')
            ->with(['licence' => 222], ['name' => 'lva-licence'])
            ->andReturn('url');

        $data = [
            'id' => 111,
            'status' => $status,
            'licence' => [
                'id' => 222,
                'licNo' => 'AB123',
                'organisation' => [
                    'name' => 'Foo ltd'
                ],
            ]
        ];

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($data);

        $this->mockQuerySender->shouldReceive('send')->once()
            ->with(m::type(VariationQry::class))
            ->andReturn($response);

        $this->sut->onVariationFurniture($event);
    }
}
