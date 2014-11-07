<?php

/**
 * Fees action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Traits;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Fees action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeesActionTraitTest extends AbstractHttpControllerTestCase
{
    protected $post = [];
    /**
     * Set up
     */
    public function setUpAction(
        $controllerName = '\Olcs\Controller\Licence\LicenceController',
        $mockGetForm = true,
        $noContent = true
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

        $mockRedirect = $this->getMock('\StdClass', ['toRoute']);
        $mockRedirect->expects($this->any())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        if ($noContent) {
            $mockResponse = $this->getMock('\StdClass', ['getContent']);
            $mockResponse->expects($this->any())
                ->method('getContent')
                ->will($this->returnValue(''));

            $this->controller->expects($this->any())
                ->method('getResponse')
                ->will($this->returnValue($mockResponse));
        }
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
     * Test fees action
     * @group feesTrait
     * @dataProvider feesForLicenceProvider
     */
    public function testFeesActionForLicence($status, $feeStatus)
    {
        $this->setUpAction('\Olcs\Controller\Licence\LicenceController');

        $params = $this->getMock('\stdClass', ['fromRoute', 'fromQuery']);

        $params->expects($this->once())
            ->method('fromRoute')
            ->with('licence')
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

        $mockServiceLocator = $this->getMock('\StdClass', ['get']);
        $mockServiceLocator->expects($this->any())
            ->method('get')
            ->with($this->equalTo('Olcs\Service\Data\Fee'))
            ->will($this->returnValue($mockFeeService));

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

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

    }

    /**
     * Data provider
     *
     * @return array
     */
    public function feesForLicenceProvider()
    {
        return [
            ['current', 'IN ["lfs_ot","lfs_wr"]'],
            ['all', ''],
            ['historical', 'IN ["lfs_pd","lfs_w","lfs_cn"]']
        ];
    }

    /**
     * Test fees action
     * @group feesTrait
     * @dataProvider feesForApplicationProvider
     */
    public function testFeesActionForApplication($status, $feeStatus)
    {
        $this->setUpAction('\Olcs\Controller\Application\ApplicationController');

        $params = $this->getMock('\stdClass', ['fromRoute', 'fromQuery']);

        $params->method('fromRoute')
            ->will(
                $this->returnValueMap(
                    [
                        ['application', 1],
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

        $mockServiceLocator = $this->getMock('\StdClass', ['get']);

        $mockServiceLocator->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('Olcs\Service\Data\Fee'))
            ->will($this->returnValue($mockFeeService));

        $mockApplicationHelper = $this->getMock('\stdClass', array('getStatus', 'getHeaderData'));
        $mockApplicationHelper->expects($this->once())
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

        $mockApplicationHelper->expects($this->once())
            ->method('getHeaderData')
            ->will($this->returnValue($headerData));

        $mockServiceLocator->expects($this->at(1))
            ->method('get')
            ->with('Entity\Application')
            ->will($this->returnValue($mockApplicationHelper));

        $mockServiceLocator->expects($this->at(2))
            ->method('get')
            ->with('Entity\Application')
            ->will($this->returnValue($mockApplicationHelper));

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

        $mockFeeService = $this->getMock('\StdClass', ['getFee', 'updateFee']);
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

    /**
     * Test edit fee action with post
     *
     * @group feesTrait
     * @dataProvider feePostProvider
     * @return array
     */
    public function testEditFeeActionWithPost(
        $statusId,
        $statusDescription,
        $post,
        $controllerName,
        $paramNameWithValue,
        $paramNameWithNull,
        $isXmlHttpReuest
    ) {
        $this->post = $post;

        $this->setUpAction($controllerName, false, false);
        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $this->request->expects($this->any())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue($isXmlHttpReuest));

        $mockUrl = $this->getMock('\StdClass', ['fromRoute']);
        $mockUrl->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue('location'));

        $this->controller->expects($this->any())
            ->method('url')
            ->will($this->returnValue($mockUrl));

        $mockResponse = $this->getMock(
            '\StdClass',
            ['getHeaders', 'setContent', 'setHeaders', 'getContent', 'addHeaders']
        );
        $mockResponse->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($mockResponse));

        $mockResponse->expects($this->any())
            ->method('addHeaders')
            ->will($this->returnValue(true));

        $mockResponse->expects($this->any())
            ->method('setContent')
            ->will($this->returnValue(true));

        $mockResponse->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue('content'));

        $this->controller->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($mockResponse));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    [
                        [$paramNameWithValue, 1],
                        [$paramNameWithNull, null]
                    ]
                )
            );

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

        $mockFeeService = $this->getMock('\StdClass', ['getFee', 'updateFee']);
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

        $this->assertEquals($mockResponse, $response);

    }

    /**
     * Data provider
     *
     * @return array
     */
    public function feePostProvider()
    {
        return [
            [
                'lfs_ot',
                'Outstanding',
                [
                    'buttonClicked' => 'form-actions[recommend]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'recommend' => ''
                    ]
                ],
                '\Olcs\Controller\Licence\LicenceController',
                'licence',
                'application',
                true
            ],
            [
                'lfs_wr',
                'Waive recommended',
                [
                    'buttonClicked' => 'form-actions[reject]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'reject' => ''
                    ]
                ],
                '\Olcs\Controller\Licence\LicenceController',
                'licence',
                'application',
                true
            ],
            [
                'lfs_wr',
                'Waive recommended',
                [
                    'buttonClicked' => 'form-actions[approve]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'approve' => ''
                    ]
                ],
                '\Olcs\Controller\Licence\LicenceController',
                'licence',
                'application',
                true
            ],
            [
                'lfs_wr',
                'Waive recommended',
                [
                    'buttonClicked' => 'form-actions[cancel]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'cancel' => ''
                    ]
                ],
                '\Olcs\Controller\Licence\LicenceController',
                'licence',
                'application',
                true
            ],
            [
                'lfs_ot',
                'Outstanding',
                [
                    'buttonClicked' => 'form-actions[recommend]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'recommend' => ''
                    ]
                ],
                '\Olcs\Controller\Licence\LicenceController',
                'licence',
                'application',
                true
            ],
            [
                'lfs_ot',
                'Outstanding',
                [
                    'buttonClicked' => 'form-actions[recommend]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'recommend' => ''
                    ]
                ],
                '\Olcs\Controller\Application\ApplicationController',
                'applicaitonId',
                'licence',
                true
            ],
            [
                'lfs_wr',
                'Waive recommended',
                [
                    'buttonClicked' => 'form-actions[reject]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'reject' => ''
                    ]
                ],
                '\Olcs\Controller\Application\ApplicationController',
                'applicaitonId',
                'licence',
                true
            ],
            [
                'lfs_wr',
                'Waive recommended',
                [
                    'buttonClicked' => 'form-actions[approve]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'approve' => ''
                    ]
                ],
                '\Olcs\Controller\Application\ApplicationController',
                'applicaitonId',
                'licence',
                true
            ],
            [
                'lfs_wr',
                'Waive recommended',
                [
                    'buttonClicked' => 'form-actions[cancel]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'cancel' => ''
                    ]
                ],
                '\Olcs\Controller\Application\ApplicationController',
                'applicaitonId',
                'licence',
                true
            ],
            [
                'lfs_ot',
                'Outstanding',
                [
                    'buttonClicked' => 'form-actions[recommend]',
                    'fee-details' => [
                        'id' => 1,
                        'version' => 1,
                        'waiveReason' => 'waive reason'
                    ],
                    'form-actions' => [
                        'recommend' => ''
                    ]
                ],
                '\Olcs\Controller\Application\ApplicationController',
                'applicaitonId',
                'licence',
                false
            ],
        ];
    }
}
