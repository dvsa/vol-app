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
 * @NOTE These tests are ported from the FeesActionTraitTest due to the movement of the feesAction method
 *  There is another application controller test file that contains more up to date tests
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController2Test extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->markTestSkipped();
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
                'getLicence',
                'getFees'
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
    public function testFeesAction($status)
    {
        $params = $this->getMock('\stdClass', ['fromRoute', 'fromQuery']);

        $params->expects($this->once())
            ->method('fromRoute')
            ->with('application')
            ->willReturn(1);

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

        $mockApplicationService = $this->getMock(
            '\stdClass',
            array('getLicenceIdForApplication', 'getHeaderData')
        );
        $mockApplicationService->expects($this->once())
            ->method('getLicenceIdForApplication')
            ->with(1)
            ->will($this->returnValue(1));

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
        $sm->setService('Entity\Application', $mockApplicationService);
        $sm->setService('Script', $mockScriptService);

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $feesParams = [
            'licence' => 1,
            'page'    => '1',
            'sort'    => 'receivedDate',
            'order'   => 'DESC',
            'limit'   => 10,
            'status'  => $status,
        ];

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

        $this->controller->expects($this->once())
            ->method('getFees')
            ->with($this->equalTo($feesParams))
            ->will($this->returnValue($fees));

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

        $mockTable = $this->getMock('\StdClass', ['removeAction']);
        $mockTable->expects($this->once())
            ->method('removeAction')
            ->with($this->equalTo('new'));
        $this->controller->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue($mockTable));

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
            ['current'],
            ['all'],
            ['historical']
        ];
    }
}
