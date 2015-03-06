<?php

namespace OlcsTest\Controller\Ebsr;

use Olcs\Controller\Ebsr\BusRegistrationController;
use Mockery as m;
use Sortable\Fixture\Transport\Bus;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Class BusRegistrationControllerTest
 * @package OlcsTest\Controller\Ebsr
 */
class BusRegistrationControllerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testDetailsActionLatestPublication()
    {
        $busRegId = 5;
        $routeNo = 123123;
        $registrationDetails = $this->generateRegistrationDetails($busRegId, $routeNo);

        $variationHistory = [
            [
                'id' => 2
            ]
        ];
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['url' => 'Url','params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');

        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-reg-variation-history', $variationHistory, m::type('array'), false)
            ->andReturn('table');

        $mockDataService = m::mock('Common\Service\Data\BusReg');
        $mockDataService->shouldReceive('fetchDetail')->with($busRegId)->andReturn($registrationDetails);
        $mockDataService->shouldReceive('fetchVariationHistory')->with($routeNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);

        $sut = new BusRegistrationController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals('table', $result->variationHistoryTable);
        $this->assertEquals(9912, $result->registrationDetails['npRreferenceNo']);
    }

    /**
     * Tests no BusRegId
     * @expectedException Common\Exception\ResourceNotFoundException
     */
    public function testDetailsActionInvalidBusRegId()
    {
        $busRegId = 5;
        $routeNo = 123123;

        $variationHistory = [
            [
                'id' => 2
            ]
        ];
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['url' => 'Url','params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');

        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-reg-variation-history', $variationHistory, m::type('array'), false)
            ->andReturn('table');

        $mockDataService = m::mock('Common\Service\Data\BusReg');
        $mockDataService->shouldReceive('fetchDetail')->with($busRegId)->andReturnNull();
        $mockDataService->shouldReceive('fetchVariationHistory')->with($routeNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);

        $sut = new BusRegistrationController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals('table', $result->variationHistoryTable);
        $this->assertEquals(9912, $result->registrationDetails['npRreferenceNo']);
    }

    /**
     * Tests no publication data
     */
    public function testDetailsActionNoValidPublicationData()
    {
        $busRegId = 5;
        $routeNo = 123123;
        $registrationDetails = $this->generateRegistrationDetails($busRegId, $routeNo);
        $registrationDetails['licence']['publicationLinks'] = [
            0 => [
                'publication' => [
                    'id' => 10,
                    'pubDate' => '2000-01-11',
                    'pubType' => 'A&D',
                    'publicationNo' => 9910
                ]
            ]
        ];

        $variationHistory = [
            [
                'id' => 2
            ]
        ];

        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['url' => 'Url','params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');

        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-reg-variation-history', $variationHistory, m::type('array'), false)
            ->andReturn('table');

        $mockDataService = m::mock('Common\Service\Data\BusReg');
        $mockDataService->shouldReceive('fetchDetail')->with($busRegId)->andReturn($registrationDetails);
        $mockDataService->shouldReceive('fetchVariationHistory')->with($routeNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);

        $sut = new BusRegistrationController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals('table', $result->variationHistoryTable);
        $this->assertNull($result->registrationDetails['npRreferenceNo']);
    }

    private function generateRegistrationDetails($busRegId, $routeNo)
    {
        return [
            'id' => $busRegId,
            'routeNo' => $routeNo,
            'licence' => [
                'id' => 110,
                'publicationLinks' => [
                    0 => [
                        'publication' => [
                            'id' => 10,
                            'pubDate' => '2000-01-11',
                            'pubType' => 'A&D',
                            'publicationNo' => 9910
                        ]
                    ],
                    1 => [
                        'publication' => [
                            'id' => 11,
                            'pubDate' => '2000-01-03',
                            'pubType' => 'N&P',
                            'publicationNo' => 9911
                        ]
                    ],
                    2 => [
                        'publication' => [
                            'id' => 12,
                            'pubDate' => '2000-01-05',
                            'pubType' => 'N&P',
                            'publicationNo' => 9912 // latest
                        ]
                    ],
                    3 => [
                        'publication' => [
                            'id' => 13,
                            'pubDate' => '2000-01-04',
                            'pubType' => 'N&P',
                            'publicationNo' => 9913
                        ]
                    ]
                ]
            ]
        ];
    }
}
