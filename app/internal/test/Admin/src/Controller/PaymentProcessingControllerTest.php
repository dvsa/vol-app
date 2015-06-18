<?php

/**
 * Payment Processing Controller Test
 */
namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Payment Processing Controller Test
 *
 * The controller under test is heavily dependent on existing FeesActionTrait
 * code, so the unit test is largely based on OlcsTest\Controller\Bus\BusControllerTest
 */
class PaymentProcessingControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            'Admin\Controller\PaymentProcessingController',
            array(
                'renderView',
                'loadScripts',
                'params',
                'getServiceLocator',
                'getRequest',
                'getForm',
                'getTable',
                'makeRestCall',
                'getService',
                'redirect',
                'commonPayFeesAction'
            )
        );

        $query = new \Zend\Stdlib\Parameters();
        $request = $this->getMock('\stdClass', ['getQuery', 'isXmlHttpRequest', 'isPost', 'getPost']);
        $request->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->query = $query;
        $this->request = $request;

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $this->view = $this->getMock(
            '\Zend\View\Model\ViewModel',
            array(
                'setTemplate'
            )
        );

        parent::setUp();
    }

    /**
     * Test feesAction
     *
     * @dataProvider feesActionProvider
     */
    public function testIndexAction($status, $feeStatus)
    {
        $this->markTestSkipped('TODO');

        $params = $this->getMock('\stdClass', ['fromRoute', 'fromQuery']);

        $params->expects($this->any())
            ->method('fromQuery')
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

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $feesParams = [
            'page'    => '1',
            'sort'    => 'receivedDate',
            'order'   => 'DESC',
            'limit'   => 10,
        ];
        if (!empty($feeStatus)) {
            $feesParams['feeStatus'] = $feeStatus;
        }
        $feesParams['bundle'] = [
            'children' => [
                'feeType' => [
                    'criteria' => ['isMiscellaneous' => true],
                    'required' => true,
                ],
            ],
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

        $mockFeeService = $this->getMock('\StdClass', ['getFees']);
        $mockFeeService->expects($this->once())
            ->method('getFees')
            ->with($this->equalTo($feesParams))
            ->will($this->returnValue($fees));

        $mockContainer = $this->getMock('\StdClass', ['set']);
        $mockContainer
            ->expects($this->once())
            ->method('set');
        $mockPlaceholder = $this->getMock('\StdClass', ['getContainer']);
        $mockPlaceholder
            ->expects($this->any())
            ->method('getContainer')
            ->with('tableFilters')
            ->will($this->returnValue($mockContainer));
        $mockViewHelperManager = $this->getMock('\StdClass', ['get']);
        $mockViewHelperManager
            ->expects($this->any())
            ->method('get')->with('placeholder')
            ->will($this->returnValue($mockPlaceholder));

        $mockServiceLocator = $this->getMock('\StdClass', ['get']);
        $mockServiceLocator->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($service) use ($mockFeeService, $mockViewHelperManager) {
                        switch ($service) {
                            case 'Olcs\Service\Data\Fee':
                                return $mockFeeService;
                            case 'viewHelperManager':
                                return $mockViewHelperManager;
                        }
                    }
                )
            );

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

        $this->controller->indexAction();
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function feesActionProvider()
    {
        return [
            ['current', 'IN ["lfs_ot","lfs_wr"]'],
            ['all', ''],
            ['historical', 'IN ["lfs_pd","lfs_w","lfs_cn"]']
        ];
    }

    /**
     * Test feesAction with invalid POST params
     */
    public function testFeesActionWithInvalidPostRedirectsCorrectly()
    {
        $this->request->expects($this->any())
            ->method('isPost')
            ->willReturn(true);

        $this->request->expects($this->any())
            ->method('getPost')
            ->willReturn([]);

        $redirect = $this->getMock('\stdClass', ['toRouteAjax']);

        $routeParams = [];

        $redirect->expects($this->once())
            ->method('toRouteAjax')
            ->with('admin-dashboard/admin-payment-processing/misc-fees', $routeParams)
            ->willReturn('REDIRECT');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->willReturn($redirect);

        $this->assertEquals('REDIRECT', $this->controller->feesAction());
    }

    public function testPayFeesActionWithGet()
    {
        $this->controller->expects($this->once())
            ->method('commonPayFeesAction')
            ->willReturn('stubResponse');

        $this->assertEquals(
            'stubResponse',
            $this->controller->payFeesAction()
        );
    }

    /**
     * Test redirect action
     */
    public function testRedirectAction()
    {
        $redirect = $this->getMock('\stdClass', ['toRouteAjax']);

        $routeParams = ['action' => 'index'];

        $redirect->expects($this->once())
            ->method('toRouteAjax')
            ->with('admin-dashboard/admin-payment-processing/misc-fees', $routeParams)
            ->willReturn('REDIRECT');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->willReturn($redirect);

        $this->assertEquals('REDIRECT', $this->controller->redirectAction());
    }
}
