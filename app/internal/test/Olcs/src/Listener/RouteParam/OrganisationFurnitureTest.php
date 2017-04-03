<?php

/**
 * Organisation Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Listener\RouteParam;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Mockery\Adapter\Phpunit\MockeryTestCase as MockeryTestCase;
use Olcs\Event\RouteParam;
use Mockery as m;
use Olcs\Listener\RouteParam\OrganisationFurniture;
use Olcs\Listener\RouteParams;
use Zend\View\Model\ViewModel;

/**
 * Organisation Furniture Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OrganisationFurnitureTest extends MockeryTestCase
{
    /**
     * @var OrganisationFurniture
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new OrganisationFurniture();
    }

    public function setupOrganisation($orgData)
    {
        $mockQuerySender = m::mock(QuerySender::class);
        $this->sut->setQuerySender($mockQuerySender);

        $mockResponse = m::mock();
        $mockQuerySender->shouldReceive('send')->once()->andReturn($mockResponse);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($orgData);
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'organisation', [$this->sut, 'onOrganisation'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnOrganisationNotFound()
    {
        $id = 1;

        $mockQuerySender = m::mock(QuerySender::class);
        $this->sut->setQuerySender($mockQuerySender);

        $mockResponse = m::mock();
        $mockQuerySender->shouldReceive('send')->once()->andReturn($mockResponse);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);

        $event = new RouteParam();
        $event->setValue($id);

        $this->setExpectedException(\Common\Exception\ResourceNotFoundException::class);

        $this->sut->onOrganisation($event);
    }

    public function testOnOrganisationLicensed()
    {
        $id = 1;
        $isMlh = true;
        $orgData = [
            'name' => 'org name',
            'isIrfo' => 'N',
            'isDisqualified' => true,
            'isUnlicensed' => false,
            'organisationIsMlh' => $isMlh
        ];

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')->once()
            ->with('horizontalNavigationId')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->with('operator')->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('right')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with(m::type(ViewModel::class))
                    ->andReturnUsing(
                        function ($view) {
                            $this->assertEquals('sections/operator/partials/right', $view->getTemplate());
                        }
                    )
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()->with('organisationIsMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($isMlh)
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()->with('isMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('')
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with('org name')
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $this->setupOrganisation($orgData);

        $event = new RouteParam();
        $event->setValue($id);

        $this->sut->onOrganisation($event);
    }

    public function testOnOrganisationUnlicensed()
    {
        $id = 1;
        $isMlh = true;
        $orgData = [
            'name' => 'org name',
            'isIrfo' => 'N',
            'isDisqualified' => true,
            'isUnlicensed' => true,
            'licence' => [
                'licNo' => 'AB123456'
            ],
            'organisationIsMlh' => $isMlh
        ];

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')->once()
            ->with('horizontalNavigationId')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()->with('operator')->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('pageTitle')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with('org name')
                    ->getMock()
            )
            ->shouldReceive('getContainer')
            ->with('right')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with(m::type(ViewModel::class))
                    ->andReturnUsing(
                        function ($view) {
                            $this->assertEquals('sections/operator/partials/right', $view->getTemplate());
                        }
                    )
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getContainer')->once()->with('organisationIsMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with($isMlh)
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()->with('isMlh')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('')
                    ->getMock()
            )
            ->shouldReceive('getContainer')->once()
            ->with('pageSubtitle')
            ->andReturn(
                m::mock()->shouldReceive('set')->once()
                    ->with('AB123456')
                    ->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $this->setupOrganisation($orgData);

        $event = new RouteParam();
        $event->setValue($id);

        $this->sut->onOrganisation($event);
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

        $service = $this->sut->createService($mockSl);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame($mockQuerySender, $this->sut->getQuerySender());
        $this->assertSame($mockCommandSender, $this->sut->getCommandSender());
    }
}
