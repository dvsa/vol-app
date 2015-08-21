<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\LicenceTitle;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class LicenceTitleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceTitleTest extends TestCase
{
    public function setUp()
    {
        $this->sut = new LicenceTitle();

        $this->mockQueryService = m::mock();
        $this->mockAnnotationBuilderService = m::mock();
        $this->mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);

        $this->sut->setAnnotationBuilderService($this->mockAnnotationBuilderService);
        $this->sut->setQueryService($this->mockQueryService);
        $this->sut->setViewHelperManager($this->mockViewHelperManager);
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$this->sut, 'onLicenceTitle'], 1);

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

    public function testOnLicenceTitle()
    {
        $licence = [
            'licNo' => 'LIC_NO',
            'organisation' => ['name' => 'Acme'],
            'status' => ['description' => 'DESC']
        ];

        $mockPlaceholder = m::mock();
        $mockPageTitle = m::mock();
        $mockPageSubtitle = m::mock();

        $this->mockQuery(['id' => 128,], $licence);

        $this->mockViewHelperManager->shouldReceive('get')->with('placeholder')->once()->andReturn($mockPlaceholder);

        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->once()->andReturn($mockPageTitle);
        $mockPageTitle->shouldReceive('set')->with('LIC_NO')->once();
        $mockPlaceholder->shouldReceive('getContainer')->with('pageSubtitle')->once()->andReturn($mockPageSubtitle);
        $mockPageSubtitle->shouldReceive('set')->with('Acme DESC')->once();

        $event = new RouteParam();
        $event->setValue(128);

        $this->sut->onLicenceTitle($event);
    }

    public function testOnLicenceTitleQueryError()
    {
        $this->mockQuery(['id' => 128,], false);

        $event = new RouteParam();
        $event->setValue(128);

        $this->setExpectedException(\RuntimeException::class);

        $this->sut->onLicenceTitle($event);
    }

    public function testCreateService()
    {
        $mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);
        $mockQueryService = m::mock();
        $mockAnnotationBuilderService = m::mock();

        $mockSl->shouldReceive('get')->with('ViewHelperManager')->once()
            ->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->once()
            ->andReturn($mockAnnotationBuilderService);
        $mockSl->shouldReceive('get')->with('QueryService')->once()->andReturn($mockQueryService);

        $obj = $this->sut->createService($mockSl);

        $this->assertSame($mockAnnotationBuilderService, $obj->getAnnotationBuilderService());
        $this->assertSame($mockViewHelperManager, $obj->getViewHelperManager());
        $this->assertSame($mockQueryService, $obj->getQueryService());

        $this->assertInstanceOf(LicenceTitle::class, $obj);
    }
}
