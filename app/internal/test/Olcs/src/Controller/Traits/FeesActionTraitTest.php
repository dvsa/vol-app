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
 * @todo These tests need sorting out, this doesn't fully cover the trait and the testEditFeeActionWithPost method
 *  with it's provider doesn't cover all scenarios
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

        $mockFeeService = $this->getMock('\StdClass', ['getFee']);
        $mockFeeService->expects($this->once())
            ->method('getFee')
            ->with($feeId)
            ->will($this->returnValue($feeDetails));

        $mockServiceLocator = Bootstrap::getServiceManager();
        $mockServiceLocator->setAllowOverride(true);
        $mockServiceLocator->setService('Olcs\Service\Data\Fee', $mockFeeService);

        $mockFeeEntityService = $this->getMock('\stdClass', ['save']);
        $mockFeeEntityService->expects($this->any())
            ->method('save');
        $mockServiceLocator->setService('Entity\Fee', $mockFeeEntityService);

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
            0 => [
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
            1 => [
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
            2 => [
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
            3 => [
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
            4 => [
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
            5 => [
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
                'application',
                'licence',
                true
            ],
            6 => [
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
                'application',
                'licence',
                true
            ],
            7 => [
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
                'application',
                'licence',
                true
            ],
            8 => [
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
                'application',
                'licence',
                true
            ],
            9 => [
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
                'application',
                'licence',
                false
            ],
        ];
    }
}
