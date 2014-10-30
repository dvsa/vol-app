<?php

/**
 * CaseController Test
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\CaseController;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * CaseController Test
 */
class CaseControllerTest extends ControllerTestAbstract
{
    protected $testClass = 'Olcs\Controller\Cases\CaseController';

    protected $proxyMethdods = [
        'redirectAction' => 'redirectToRoute',
        'indexAction' => 'redirectToRoute'
    ];

    public function testGetCase()
    {
        $caseId = 29;
        $case = ['id' => 29];

        $mockService = m::mock('Olcs\Service\Data\Cases');
        $mockService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockService);

        $sut = new CaseController();
        $sut->setServiceLocator($mockSl);

        $this->assertEquals($case, $sut->getCase($caseId));
    }

    public function testGetCaseWithId()
    {
        $caseId = 29;
        $case = ['id' => 29];

        $helper = new ControllerPluginManagerHelper();
        $mockPluginManager = $helper->getMockPluginManager(['params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockService = m::mock('Olcs\Service\Data\Cases');
        $mockService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockService);

        $sut = new CaseController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $this->assertEquals($case, $sut->getCase());
    }
}
