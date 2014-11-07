<?php

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Application;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Application\ApplicationController',
            array(
                'getRequest',
                'params',
                'redirect',
                'getTable',
                'getForm',
                'getFromRoute',
                'getLicence'
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
     * @group application_controller
     *
     * @dataProvider feesForApplicationProvider
     */
    public function testFeesAction($status, $feeStatus)
    {
        $params = $this->getMock('\stdClass', ['fromRoute', 'fromQuery']);

        $params->expects($this->once())
            ->method('fromRoute')
            ->with('application')
            ->will($this->returnValue(1));

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

        $mockApplicationService = $this->getMock(
            '\stdClass',
            array('getLicenceIdForApplication', 'getStatus', 'getHeaderData')
        );
        $mockApplicationService->expects($this->once())
            ->method('getLicenceIdForApplication')
            ->with(1)
            ->will($this->returnValue(1));

        $mockApplicationService->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION));

        $headerData = array(
            'id' => 1,
            'status' => array(
                'id' => 'Foo'
            ),
            'licence' => array(
                'id' => 123,
                'licNo' => 'asdjlkads',
                'organisation' => array(
                    'name' => 'sdjfhkjsdhf'
                )
            )
        );

        $mockApplicationService->expects($this->once())
            ->method('getHeaderData')
            ->will($this->returnValue($headerData));

        $mockScriptService = $this->getMock('\stdClass', array('loadFiles'));

        $sm = \OlcsTest\Bootstrap::getServiceManager();
        $sm->setAllowOverride(true);
        $sm->setService('Entity\Application', $mockApplicationService);
        $sm->setService('Script', $mockScriptService);

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $mockFeeService = $this->getMock('\stdClass', ['getFees']);

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
                    'id' => 1,
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

        $sm->setService('Olcs\Service\Data\Fee', $mockFeeService);

        $this->controller->setServiceLocator($sm);

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

        $this->assertInstanceOf('\Olcs\View\Model\Application\Layout', $response);

    }

    /**
     * Data provider
     *
     * @return array
     */
    public function feesForApplicationProvider()
    {
        return [
            ['current', 'IN ["lfs_ot","lfs_wr"]'],
            ['all', ''],
            ['historical', 'IN ["lfs_pd","lfs_w","lfs_cn"]']
        ];
    }
}
