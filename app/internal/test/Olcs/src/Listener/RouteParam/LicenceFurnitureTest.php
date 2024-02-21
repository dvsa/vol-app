<?php

namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Laminas\View\HelperPluginManager;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\LicenceFurniture;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\RefData;
use Laminas\View\Model\ViewModel;

class LicenceFurnitureTest extends TestCase
{
    /**
     * @var LicenceFurniture
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new LicenceFurniture();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Laminas\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$this->sut, 'onLicenceFurniture'], 1);

        $this->sut->attach($mockEventManager);
    }

    protected function onLicenceSetup($licenceData)
    {
        $mockQuerySender = m::mock(QuerySender::class);
        $this->sut->setQuerySender($mockQuerySender);

        $mockResult = m::mock();

        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockQuerySender->shouldReceive('send')->once()->andReturn($mockResult);

        if ($licenceData === false) {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResult->shouldReceive('getResult')->with()->once()->andReturn($licenceData);
        }
    }

    public function testOnLicenceQueryError()
    {
        $this->onLicenceSetup(false);
        $routeParam = new RouteParam();
        $routeParam->setValue(32);

        $event = new Event(null, $routeParam);

        $this->expectException(ResourceNotFoundException::class);

        $this->sut->onLicenceFurniture($event);
    }

    public function testOnLicence()
    {
        $licenceId = 4;
        $isMlh = true;
        $licence = [
            'id' => $licenceId,
            'organisation' => [
                'name' => 'Foo ltd'
            ],
            'licNo' => 'AB12345',
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'isMlh' => $isMlh
        ];

        $this->onLicenceSetup($licence);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')->once()
            ->with('status')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('horizontalNavigationId')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->with('licence')->getMock()
            )
            ->shouldReceive('getContainer')->once()->with('isMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($isMlh)
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('right')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with(m::type(ViewModel::class))
                    ->andReturnUsing(
                        function ($view) {
                            $this->assertEquals('sections/licence/partials/right', $view->getTemplate());
                        }
                    )
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with('AB12345')
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with('Foo ltd')
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock(HelperPluginManager::class)
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicenceFurniture($event);
    }

    public function testInvoke()
    {
        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockQuerySender = m::mock(QuerySender::class);
        $mockCommandSender = m::mock(CommandSender::class);
        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once();

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $mockSl->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);
        $mockSl->shouldReceive('get')->with('EventManager')->andReturn($mockEventManager);

        $sut = new LicenceFurniture();
        $service = $sut->__invoke($mockSl, LicenceFurniture::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockQuerySender, $sut->getQuerySender());
        $this->assertSame($mockCommandSender, $sut->getCommandSender());
    }
}
