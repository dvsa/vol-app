<?php

/**
 * Application Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Command\Audit\ReadApplication;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\ApplicationFurniture;
use Mockery as m;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ViewModel;

/**
 * Application Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationFurnitureTest extends TestCase
{
    /**
     * @var ApplicationFurniture
     */
    protected $sut;

    protected $mockViewHelperManager;
    protected $mockQuerySender;
    protected $mockCommandSender;
    protected $mockRouter;

    public function setUp()
    {
        $this->mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->mockQuerySender = m::mock(QuerySender::class);
        $this->mockCommandSender = m::mock(CommandSender::class);
        $this->mockRouter = m::mock(RouteStackInterface::class);

        $this->sut = new ApplicationFurniture();

        $sl = m::mock(ServiceLocatorInterface::class);

        $sl->shouldReceive('get')->with('ViewHelperManager')->andReturn($this->mockViewHelperManager);
        $sl->shouldReceive('get')->with('QuerySender')->andReturn($this->mockQuerySender);
        $sl->shouldReceive('get')->with('Router')->andReturn($this->mockRouter);
        $sl->shouldReceive('get')->with('CommandSender')->andReturn($this->mockCommandSender);

        $this->sut->createService($sl);
    }

    public function testAttach()
    {
        $events = m::mock(EventManagerInterface::class);

        $events->shouldReceive('attach')->once()
            ->with('route.param.application', [$this->sut, 'onApplicationFurniture'], 1)
            ->andReturn('listener');

        $this->sut->attach($events);
    }

    public function testOnApplicationFurnitureWithError()
    {
        $this->setExpectedException(ResourceNotFoundException::class);

        $event = m::mock(RouteParam::class);
        $event->shouldReceive('getValue')->andReturn(111);

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(false);

        $this->mockQuerySender->shouldReceive('send')->once()
            ->with(m::type(ApplicationQry::class))
            ->andReturn($response);

        $this->sut->onApplicationFurniture($event);
    }

    public function testOnApplicationFurnitureValid()
    {
        $event = m::mock(RouteParam::class);
        $event->shouldReceive('getValue')->andReturn(111);

        $status = [
            'id' => RefData::APPLICATION_STATUS_VALID
        ];

        $isMlh = true;

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
            ->with('isMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($isMlh)
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
            ],
            'isVariation' => 0,
            'isMlh' => $isMlh
        ];

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($data);

        $this->mockQuerySender->shouldReceive('send')->once()
            ->with(m::type(ApplicationQry::class))
            ->andReturn($response);

        $this->sut->onApplicationFurniture($event);
    }

    public function testOnApplicationFurnitureIsVariationValid()
    {
        $event = m::mock(RouteParam::class);
        $event->shouldReceive('getValue')->andReturn(111);

        $status = [
            'id' => RefData::APPLICATION_STATUS_NOT_SUBMITTED
        ];
        $isMlh = true;

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
            ->with('isMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($isMlh)
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
            ],
            'isVariation' => 1,
            'isMlh' => $isMlh
        ];

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($data);

        $this->mockQuerySender->shouldReceive('send')->once()
            ->with(m::type(ApplicationQry::class))
            ->andReturn($response);

        $this->sut->onApplicationFurniture($event);
    }

    public function testOnApplicationFurnitureNotSubmitted()
    {
        $event = m::mock(RouteParam::class);
        $event->shouldReceive('getValue')->andReturn(111);

        $status = [
            'id' => RefData::APPLICATION_STATUS_NOT_SUBMITTED
        ];
        $isMlh = true;

        $mockPlaceholder = m::mock();
        $mockPlaceholder->shouldReceive('getContainer')
            ->with('pageTitle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('AB123 / 111')
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
            ->with('isMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($isMlh)
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

        $data = [
            'id' => 111,
            'status' => $status,
            'licence' => [
                'id' => 222,
                'licNo' => 'AB123',
                'organisation' => [
                    'name' => 'Foo ltd'
                ],
            ],
            'isVariation' => 0,
            'isMlh' => $isMlh
        ];

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($data);

        $this->mockQuerySender->shouldReceive('send')->once()
            ->with(m::type(ApplicationQry::class))
            ->andReturn($response);

        $this->sut->onApplicationFurniture($event);
    }

    public function testOnApplicationFurnitureNotSubmittedNoLicNo()
    {
        $event = m::mock(RouteParam::class);
        $event->shouldReceive('getValue')->andReturn(111);

        $status = [
            'id' => RefData::APPLICATION_STATUS_NOT_SUBMITTED
        ];
        $isMlh = true;

        $mockPlaceholder = m::mock();
        $mockPlaceholder->shouldReceive('getContainer')
            ->with('pageTitle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('111')
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
            ->with('isMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($isMlh)
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

        $data = [
            'id' => 111,
            'status' => $status,
            'licence' => [
                'id' => 222,
                'licNo' => null,
                'organisation' => [
                    'name' => 'Foo ltd'
                ],
            ],
            'isVariation' => 0,
            'isMlh' => $isMlh
        ];

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($data);

        $this->mockQuerySender->shouldReceive('send')->once()
            ->with(m::type(ApplicationQry::class))
            ->andReturn($response);

        $this->sut->onApplicationFurniture($event);
    }
}
