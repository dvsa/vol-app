<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\LicenceTitleLink;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class LicenceTitleLinkTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceTitleLinkTest extends TestCase
{
    public function setUp()
    {
        $this->sut = new LicenceTitleLink();

        $this->mockQueryService = m::mock();
        $this->mockAnnotationBuilderService = m::mock();
        $this->mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);
        $this->mockRouter = m::mock();

        $this->sut->setAnnotationBuilderService($this->mockAnnotationBuilderService);
        $this->sut->setQueryService($this->mockQueryService);
        $this->sut->setViewHelperManager($this->mockViewHelperManager);
        $this->sut->setRouter($this->mockRouter);
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$this->sut, 'onLicenceTitleLink'], 1);

        $this->sut->attach($mockEventManager);
    }

    protected function mockQuery($expectedDtoParams, $result = false)
    {
        $mockResponse = m::mock();

        $this->mockAnnotationBuilderService->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($expectedDtoParams) {
                $this->assertSame($expectedDtoParams, $dto->getArrayCopy());
                return 'QUERY';
            }
        );

        $this->mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResponse);
        if ($result === false) {
            $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResponse->shouldReceive('getResult')->with()->once()
                ->andReturn($result);
        }
    }

    public function testOnLicenceTitleLink()
    {
        $licence = [
            'id' => 128,
            'licNo' => 'LIC_NO',
            'organisation' => ['name' => 'Acme'],
            'status' => ['description' => 'DESC']
        ];

        $mockPlaceholder = m::mock();
        $mockPageTitle = m::mock();

        $this->mockQuery(['id' => 128,], $licence);

        $this->mockViewHelperManager->shouldReceive('get')->with('placeholder')->once()->andReturn($mockPlaceholder);
        $this->mockRouter->shouldReceive('assemble')->with(['licence' => 128], ['name' => 'licence/cases'])->once()
            ->andReturn('URL');

        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->once()->andReturn($mockPageTitle);
        $mockPageTitle->shouldReceive('prepend')->with('<a href="URL">LIC_NO</a>')->once();

        $event = new RouteParam();
        $event->setValue(128);

        $this->sut->onLicenceTitleLink($event);
    }

    public function testOnLicenceTitleLinkQueryError()
    {
        $this->mockQuery(['id' => 128,], false);

        $event = new RouteParam();
        $event->setValue(128);

        $this->setExpectedException(\RuntimeException::class);

        $this->sut->onLicenceTitleLink($event);
    }

    public function testCreateService()
    {
        $mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);
        $mockQueryService = m::mock();
        $mockAnnotationBuilderService = m::mock();
        $mockRouter = m::mock();

        $mockSl->shouldReceive('get')->with('ViewHelperManager')->once()
            ->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->once()
            ->andReturn($mockAnnotationBuilderService);
        $mockSl->shouldReceive('get')->with('QueryService')->once()->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with('Router')->once()->andReturn($mockRouter);

        $obj = $this->sut->createService($mockSl);

        $this->assertSame($mockAnnotationBuilderService, $obj->getAnnotationBuilderService());
        $this->assertSame($mockViewHelperManager, $obj->getViewHelperManager());
        $this->assertSame($mockQueryService, $obj->getQueryService());
        $this->assertSame($mockRouter, $obj->getRouter());

        $this->assertInstanceOf(LicenceTitleLink::class, $obj);
    }
}
