<?php

/**
 * Fees action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Traits;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;

/**
 * Fees action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 *
 * @NOTE I have removed a test from this class, as it isn't testing what it appeared to be testing
 */
class FeesActionTraitTest extends AbstractHttpControllerTestCase
{
    protected $post = [];

    protected $mockRedirect;

    /**
     * Set up
     */
    public function setUpAction(
        $controllerName = '\Olcs\Controller\Licence\LicenceController',
        $mockGetForm = true
    ) {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );

        $methods = [
            'makeRestCall',
            'getLoggedInUser',
            'getLicence',
            'getRequest',
            'loadScripts',
            'getFromRoute',
            'params',
            'redirect',
            'getServiceLocator',
            'getTable',
            'url',
            'setTableFilters',
            'getSearchForm',
            'setupMarkers',
            'getResponse',
        ];
        if ($mockGetForm) {
            $methods[] = 'getForm';
        }
        $this->controller = $this->getMock(
            $controllerName,
            $methods
        );

        $this->mockRedirect = $this->getMock('\StdClass', ['toRoute', 'toRouteAjax']);
        $this->mockRedirect->expects($this->any())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($this->mockRedirect));

        $mockResponse = $this->getMock('\StdClass', ['getContent']);
        $mockResponse->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue(''));

        $this->controller->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($mockResponse));

        $query = new \Zend\Stdlib\Parameters();
        $request = $this->getMock('\stdClass', ['getQuery', 'isXmlHttpRequest', 'isPost', 'getUri', 'getPost']);
        $request->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($this->post));

        $mockUri = $this->getMock('\StdClass', ['getPath']);
        $mockUri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));

        $request->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($mockUri));

        $this->query = $query;
        $this->request = $request;

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        parent::setUp();
    }

    /**
     * Test edit fee action with form alteration
     *
     * @group feesTrait
     * @dataProvider feeStatusesProvider
     * @return array
     */
    public function testEditFeeActionWithFormAlteration($statusId, $statusDescription)
    {
        $this->setUpAction('\Olcs\Controller\Licence\LicenceController', false);

        $feeId = 1;
        $feeDetails = [
            'id' => 1,
            'description' => 'desc',
            'amount' => 123.12,
            'invoicedDate' => '2014-01-01 10:10:10',
            'receiptNo' => '123',
            'receivedAmount' => 123.12,
            'receivedDate' => '2014-01-01 10:10:10',
            'waiveReason' => 'waive reason',
            'version' => 1,
            'feeStatus' => [
                'id' => $statusId,
                'description' => $statusDescription
            ],
            'paymentMethod' => [
                'id' => 'fpm_cash',
                'description' => 'Cash'
            ],
            'lastModifiedBy' => [
                'id' => 1,
                'name' => 'Some User'
            ]
        ];

        $mockParams = $this->getMock('\StdClass', ['fromRoute']);
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('fee', null)
            ->will($this->returnValue($feeId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $mockFeeService = $this->getMock('\StdClass', ['getFee']);
        $mockFeeService->expects($this->once())
            ->method('getFee')
            ->with($feeId)
            ->will($this->returnValue($feeDetails));

        $mockServiceLocator = Bootstrap::getServiceManager();
        $mockServiceLocator->setAllowOverride(true);
        $mockServiceLocator->setService('Olcs\Service\Data\Fee', $mockFeeService);

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));

        $response = $this->controller->editFeeAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function feeStatusesProvider()
    {
        return [
            ['lfs_ot', 'Outstanding'],
            ['lfs_wr', 'Waive recommended'],
            ['lfs_w', 'Waived'],
            ['lfs_pd', 'Paid'],
            ['lfs_cn', 'Cancelled']
        ];
    }
}
