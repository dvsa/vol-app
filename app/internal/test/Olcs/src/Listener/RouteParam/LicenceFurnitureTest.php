<?php

/**
 * Licence Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\LicenceFurniture;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\RefData;
use Zend\View\Model\ViewModel;

/**
 * Licence Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceFurnitureTest extends TestCase
{
    /**
     * @var LicenceFurniture
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new LicenceFurniture();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$this->sut, 'onLicenceFurniture'], 1);

        $this->sut->attach($mockEventManager);
    }

    protected function onLicenceSetup($licenceData)
    {
        $mockQuerySender = m::mock(QuerySender::class);
        $this->sut->setQuerySender($mockQuerySender);

        $mockResult = m::mock();

        $mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);
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
        $event = new RouteParam();
        $event->setValue(32);

        $this->setExpectedException(ResourceNotFoundException::class);

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

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicenceFurniture($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockQuerySender = m::mock(QuerySender::class);
        $mockCommandSender = m::mock(CommandSender::class);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $mockSl->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);

        $sut = new LicenceFurniture();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockQuerySender, $sut->getQuerySender());
        $this->assertSame($mockCommandSender, $sut->getCommandSender());
    }
}
