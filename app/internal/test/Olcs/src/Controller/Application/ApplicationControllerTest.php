<?php

/**
 * Application controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Licence;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Appication controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Application\ApplicationController',
            array(
                'makeRestCall',
                'getLoggedInUser',
                'getLicence',
                'getRequest',
                'getForm',
                'loadScripts',
                'getFromRoute',
                'params',
                'redirect',
                'getServiceLocator',
                'getTable',
                'url',
                'setTableFilters'
            )
        );

        $query = new \Zend\Stdlib\Parameters();
        $request = $this->getMock('\stdClass', ['getQuery', 'isXmlHttpRequest', 'isPost']);
        $request->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->query = $query;
        $this->request = $request;

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        parent::setUp();
    }

    /**
     * Test fees action
     * @group applicationController
     */
    public function testFeesAction()
    {
        $params = $this->getMock('\stdClass', ['fromRoute']);

        $params->expects($this->at(0))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(null));

        $params->expects($this->at(1))
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue(1));

        $params->expects($this->at(2))
            ->method('fromRoute')
            ->with('page')
            ->will($this->returnValue(1));

        $params->expects($this->at(3))
            ->method('fromRoute')
            ->with('sort')
            ->will($this->returnValue('receivedDate'));

        $params->expects($this->at(4))
            ->method('fromRoute')
            ->with('order')
            ->will($this->returnValue('DESC'));

        $params->expects($this->at(5))
            ->method('fromRoute')
            ->with('limit')
            ->will($this->returnValue(10));

        $bundle = [
            'properties' => null,
            'children' => [
                'licence' => [
                    'properties' => [
                        'id'
                    ]
                ]
            ]
        ];

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('Application'),
                $this->equalTo('GET'),
                $this->equalTo(['id' => 1]),
                $this->equalTo($bundle)
            )
            ->will($this->returnValue(['licence' => ['id' => 1]]));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $mockFeeService = $this->getMock('\StdClass', ['getFees']);

        $feesParams = [
            'licence' => 1,
            'feeStatus' => "IN ('lfs_ot', 'lfs_wr')",
            'page'    => 1,
            'sort'    => 'receivedDate',
            'order'   => 'DESC',
            'limit'   => 10,
        ];

        $fees = [
            'Results' => [
                [
                    'invoiceNo' => 'i',
                    'invoiceStatus' => 'is',
                    'description' => 'ds',
                    'amount' => 1,
                    'invoicedDate' => '2014-01-01',
                    'receiptNo' => '1',
                    'receivedDate' => '2014-01-01',
                    'feeStatus' => [
                        'id' => 'lfs_ot',
                        'description' => 'd'
                    ]
                ]
            ],
            'Count' => 1
        ];

        $mockFeeService->expects($this->once())
            ->method('getFees')
            ->with($this->equalTo($feesParams))
            ->will($this->returnValue($fees));

        $mockApplicationJourneyHelper = $this->getMock('\StdClass', ['render']);
        $mockApplicationJourneyHelper->expects($this->once())
            ->method('render')
            ->will($this->returnValue('rendered view'));

        $mockServiceLocator = $this->getMock('\StdClass', ['get']);

        $mockServiceLocator->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('Olcs\Service\Data\Fee'))
            ->will($this->returnValue($mockFeeService));

        $mockServiceLocator->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('ApplicationJourneyHelper'))
            ->will($this->returnValue($mockApplicationJourneyHelper));

        $this->controller->expects($this->any())
             ->method('getServiceLocator')
             ->will($this->returnValue($mockServiceLocator));

        $response = $this->controller->feesAction();

        $this->assertEquals('rendered view', $response);

    }
}
