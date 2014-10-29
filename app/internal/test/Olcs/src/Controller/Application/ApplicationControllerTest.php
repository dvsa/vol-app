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
                'setTableFilters',
                'setupMarkers'
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
     * @dataProvider feesProvider
     */
    public function testFeesAction($status, $feeStatus)
    {
        $params = $this->getMock('\stdClass', ['fromRoute', 'fromQuery']);

        $params->method('fromRoute')
            ->will(
                $this->returnValueMap(
                    [
                        ['applicationId', 1],
                        ['licence', null],
                    ]
                )
            );

        $params->expects($this->any())->method('fromQuery')
            ->will(
                $this->returnValueMap(
                    [
                        ['status', $status],
                        ['page', 1, 1],
                        ['sort', 'receivedDate', 'receivedDate'],
                        ['order', 'DESC', 'DESC'],
                        ['limit', 10, 10],
                    ]
                )
            );

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
            'page'    => '1',
            'sort'    => 'receivedDate',
            'order'   => 'DESC',
            'limit'   => 10,
        ];
        if ($feeStatus) {
            $feesParams['feeStatus'] = $feeStatus;
        }

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

        $mockForm = $this->getMock('\StdClass', ['remove', 'setData']);
        $mockForm->expects($this->once())
            ->method('remove')
            ->with($this->equalTo('csrf'))
            ->will($this->returnValue(true));

        $mockForm->expects($this->once())
            ->method('setData')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($mockForm));

        $response = $this->controller->feesAction();

        $this->assertEquals('rendered view', $response);

    }

    /**
     * Goods or psv provider
     *
     * @return array
     */
    public function feesProvider()
    {
        return [
            ['current', "IN ('lfs_ot', 'lfs_wr')"],
            ['all', ''],
            ['historical', "IN ('lfs_pd', 'lfs_w', 'lfs_cn')"]
        ];
    }
}
