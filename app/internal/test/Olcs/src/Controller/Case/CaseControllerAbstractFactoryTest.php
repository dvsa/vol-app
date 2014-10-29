<?php

namespace OlcsTest\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Olcs\Controller\Cases\CaseControllerAbstractFactory;
use Mockery as m;

/**
 * Class CaseControllerAbstractFactoryTest
 * @package OlcsTest\Controller
 */
class CaseControllerAbstractFactoryTest extends TestCase
{
    protected $config = [
        'controllers' => [
            'case_controllers' => [
                'CaseController' => 'Olcs\Controller\Cases\CaseController'
            ]
        ]
    ];

    public function testCanCreateServiceWithName()
    {
        $mockSl = $this->getSlMock();

        $sut = new CaseControllerAbstractFactory();

        $this->assertTrue($sut->canCreateServiceWithName($mockSl, '', 'CaseController'));
        $this->assertFalse($sut->canCreateServiceWithName($mockSl, '', 'NotACaseController'));
    }

    public function testCreateServiceWithName()
    {
        $mockParamsListener = m::mock('Olcs\Listener\RouteParams');
        $mockParamsListener->shouldReceive('attach')->once();

        $mockSl = $this->getSlMock();
        $mockSl->shouldReceive('get')->with('RouteParamsListener')->andReturn($mockParamsListener);

        $sut = new CaseControllerAbstractFactory();
        $instance = $sut->createServiceWithName($mockSl, '', 'CaseController');

        $this->assertInstanceOf('Olcs\Controller\Cases\CaseController', $instance);
    }

    public function testCreateServiceWithNameAttachesListeners()
    {
        $mockParamsListener = m::mock('Olcs\Listener\RouteParams');
        $mockParamsListener->shouldReceive('attach');
        $mockParamsListener->shouldReceive('getEventManager->attach')->with($mockParamsListener);

        $this->config['route_param_listeners']['case_controllers'][] = 'Listener';

        $mockSl = $this->getSlMock();
        $mockSl->shouldReceive('get')->with('RouteParamsListener')->andReturn($mockParamsListener);
        $mockSl->shouldReceive('get')->with('Listener')->andReturn($mockParamsListener);

        $sut = new CaseControllerAbstractFactory();
        $instance = $sut->createServiceWithName($mockSl, '', 'CaseController');

        $this->assertInstanceOf('Olcs\Controller\Cases\CaseController', $instance);
    }

    protected function getSlMock()
    {
        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('getServiceLocator')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Config')->andReturn($this->config);
        return $mockSl;
    }
}
 