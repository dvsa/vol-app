<?php

/**
 * Licence controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Licence;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Common\Service\Entity\LicenceEntityService;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;
use OlcsTest\Bootstrap;

/**
 * Licence controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceControllerTest extends AbstractHttpControllerTestCase
{
    use ControllerTestTrait;
    use \OlcsTest\Traits\MockeryTestCaseTrait;

    /**
     * Required by trait
     */
    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    public function setUp()
    {
        $this->markTestSkipped();

        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Licence\LicenceController',
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
                'getSearchForm',
                'setupMarkers',
                'commonPayFeesAction',
                'checkForCrudAction',
                'getFees',
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

        parent::setUp();
    }

    /**
     * @group licence_controller
     *
     * @dataProvider feesForLicenceProvider
     */
    public function testFeesAction($status)
    {
        $params = $this->getMock('\stdClass', ['fromRoute', 'fromQuery']);

        $params->expects($this->once())
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(1));

        $params->expects($this->any())
            ->method('fromQuery')
            ->will(
                $this->returnValueMap(
                    [
                        ['status', $status],
                        ['page', 1, 1],
                        ['sort', 'id', 'id'],
                        ['order', 'ASC', 'ASC'],
                        ['limit', 10, 10],
                    ]
                )
            );

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $feesParams = [
            'licence' => 1,
            'page'    => '1',
            'sort'    => 'id',
            'order'   => 'ASC',
            'limit'   => 10,
            'status'  => $status,
        ];

        $fees = [
            'results' => [
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
            'count' => 1,
            'extra' => [
                'allowFeePayments' => true,
            ],
        ];

        $this->controller->expects($this->once())
            ->method('getFees')
            ->with($this->equalTo($feesParams))
            ->will($this->returnValue($fees));

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
     * Gets a mock version of translator
     */
    private function getServiceLocatorTranslator()
    {
        $translatorMock = $this->getMock('\stdClass', array('translate'));
        $translatorMock->expects($this->any())
                       ->method('translate')
                       ->will($this->returnArgument(0));

        $serviceMock = $this->getMock('\stdClass', array('get'));
        $serviceMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('translator'))
            ->will($this->returnValue($translatorMock));

        return $serviceMock;
    }

    /**
     * @group licenceController
     */
    public function testDocumentsActionWithNoQueryUsesDefaultParams()
    {
        $this->markTestSkipped();
        $licenceData = array(
            'licNo' => 'TEST1234',
            'goodsOrPsv' => array(
                'id' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                'description' => 'PSV'
            ),
            'organisation' => array(
                'name' => 'O1'
            ),
            'status' => array(
                'id' => 'S1',
                'description' => 'S1'
            )
        );

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorTranslator()));

        $this->controller->expects($this->any())
            ->method('getLicence')
            ->will($this->returnValue($licenceData));

        $this->controller->expects($this->any())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->with('licence')
            ->will($this->returnValue(1234));

        $expectedParams = array(
            'sort'   => 'issuedDate',
            'order'  => 'DESC',
            'page'   => 1,
            'limit'  => 10,
            'licenceId' => 1234
        );

        $this->controller->expects($this->at(3))
            ->method('makeRestCall')
            ->with('DocumentSearchView', 'GET', $expectedParams)
            ->will($this->returnValue([]));

        $altListData = [
            'limit' => 100,
            'sort' => 'description',
            'isDocCategory' => true,
        ];

        $altResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'description' => 'foo'
                ]
            ]
        ];

        $subResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'subCategoryName' => 'foo'
                ]
            ]
        ];

        $extendedListData = [
            'limit' => 100,
            'sort' => 'subCategoryName',
            'order' => 'ASC',
            'page' => 1,
            'licenceId' => 1234,
            'isDoc' => true
        ];

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('SubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($subResponse));

        $tableMock = $this->getMock('\stdClass');
        $this->controller->expects($this->once())
            ->method('getTable')
            ->with(
                'documents',
                [],
                array_merge(
                    $expectedParams,
                    array('query' => $this->query)
                )
            )
            ->will($this->returnValue($tableMock));

        $form = $this->getMock('\stdClass', ['get', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $view = $this->controller->documentsAction();

        $this->assertTrue($view->terminate());

    }

    public function testDetailsActionWithGoodsLicenceRemovesBusNavLink()
    {
        $licenceData = array(
            'licNo' => 'TEST1234',
            'goodsOrPsv' => array(
                'id' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                'description' => 'Goods'
            ),
            'organisation' => array(
                'name' => 'O1'
            ),
            'status' => array(
                'id' => 'S1',
                'description' => 'S1'
            )
        );

        $this->controller->expects($this->any())
            ->method('getLicence')
            ->will($this->returnValue($licenceData));

        $navItem = $this->getMock('\stdClass', ['setVisible']);
        $navItem->expects($this->once())
            ->method('setVisible')
            ->with(0);

        $navMock = $this->getMock('\stdClass', ['findOneBy']);
        $navMock->expects($this->once())
            ->method('findOneBy')
            ->with('id', 'licence_bus')
            ->willReturn($navItem);

        $slMock = $this->getMock('\stdClass', ['get']);
        $slMock->expects($this->once())
            ->method('get')
            ->with('Navigation')
            ->willReturn($navMock);

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($slMock);

        $this->controller->detailsAction();
    }

    /**
     * @group licenceController
     */
    public function testDocumentsActionAjax()
    {
        $this->markTestSkipped();
        $this->controller->expects($this->any())
             ->method('getServiceLocator')
             ->will($this->returnValue($this->getServiceLocatorTranslator()));

        $this->controller->expects($this->any())
             ->method('getServiceLocator')
             ->will($this->returnValue($this->getServiceLocatorTranslator()));

        $this->controller->expects($this->at(3))
            ->method('makeRestCall')
            ->will($this->returnValue([]));

        $form = $this->getMock('\stdClass', ['get', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $tableMock = $this->getMock('\stdClass', ['render', 'removeColumn']);

        $this->controller->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue($tableMock));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $response = [
            'Results' => [
                [
                    'id' => 123,
                    'name' => 'foo'
                ]
            ]
        ];
        $altListData = [
            'limit' => 100,
            'sort' => 'description',
            'isDocCategory' => true,
        ];

        $altResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'description' => 'foo'
                ]
            ]
        ];

        $subResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'subCategoryName' => 'foo'
                ]
            ]
        ];

        $extendedListData = [
            'limit' => 100,
            'sort' => 'subCategoryName',
            'order' => 'ASC',
            'page' => 1,
            'isDoc' => true
        ];

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('SubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($subResponse));

        $this->controller->expects($this->at(11))
            ->method('makeRestCall')
            ->will($this->returnValue($response));

        $this->request->expects($this->once())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));

        $view = $this->controller->documentsAction();

        $this->assertTrue($view->terminate());
    }

    /**
     * @group licenceController
     */
    public function testDocumentsActionWithGenerateRedirectsToGenerate()
    {
        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorTranslator()));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = $this->getMock('\stdClass', ['fromPost']);

        $params->expects($this->once())
            ->method('fromPost')
            ->with('action')
            ->will($this->returnValue('new letter'));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($params));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/documents/generate', ['licence' => 1234]);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->with('licence')
            ->will($this->returnValue(1234));

        $this->controller->documentsAction();
    }

    /**
     * @group licenceController
     */
    public function testDocumentsActionWithUploadRedirectsToUpload()
    {
        $this->controller->expects($this->any())
             ->method('getServiceLocator')
             ->will($this->returnValue($this->getServiceLocatorTranslator()));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = $this->getMock('\stdClass', ['fromPost']);

        $params->expects($this->once())
            ->method('fromPost')
            ->with('action')
            ->will($this->returnValue('upload'));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($params));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/documents/upload', ['licence' => 1234]);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->with('licence')
            ->will($this->returnValue(1234));

        $this->controller->documentsAction();
    }

    public function testFeesListActionWithValidPostRedirectsCorrectly()
    {
        $post = [
            'id' => [1,2,3]
        ];

        $this->request->expects($this->any())
            ->method('getPost')
            ->willReturn($post);

        $this->request->expects($this->any())
            ->method('isPost')
            ->willReturn(true);

        $routeParams = [
            'action' => 'pay-fees',
            'fee' => '1,2,3'
        ];

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $routeParams = [
            'action' => 'pay-fees',
            'fee' => '1,2,3'
        ];

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/fees/fee_action', $routeParams)
            ->willReturn('REDIRECT');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->willReturn($redirect);

        $this->assertEquals('REDIRECT', $this->controller->feesAction());
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

        $params = $this->getMock('\stdClass', ['fromRoute']);

        $params->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValueMap(
                    [
                        ['licence', 1],
                    ]
                )
            );

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $redirect = $this->getMock('\stdClass', ['toRouteAjax']);

        $routeParams = [
            'licence' => 1,
        ];

        $redirect->expects($this->once())
            ->method('toRouteAjax')
            ->with('licence/fees', $routeParams)
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
     * @group application_controller
     */
    public function testOppositionAction()
    {
        $this->mockController(
            '\Olcs\Controller\Licence\LicenceController'
        );

        $this->sut->shouldReceive('params->fromRoute')
            ->once()
            ->with('licence', null)
            ->andReturn(321);

        $mockOppostionResponse = \Mockery::mock()
            ->shouldReceive('isOk')->with()->once()->andReturn(true)
            ->shouldReceive('getResult')->with()->once()->andReturn(['results' => ['oppositions']])
            ->getMock();
        $this->sut->shouldReceive('handleQuery')->once()->andReturnUsing(
            function ($dto) use ($mockOppostionResponse) {
                $this->assertSame(321, $dto->getLicence());
                return $mockOppostionResponse;
            }
        );

        $mockOppositionHelperService = \Mockery::mock('\Common\Service\Helper\OppositionHelperService');
        $this->sm->setService('Helper\Opposition', $mockOppositionHelperService);
        $mockOppositionHelperService->shouldReceive('sortOpenClosed')
            ->once()
            ->with(['oppositions'])
            ->andReturn(['sorted-oppositions']);

        $mockComplaintResponse = \Mockery::mock()
            ->shouldReceive('isOk')->with()->once()->andReturn(true)
            ->shouldReceive('getResult')->with()->once()->andReturn(['results' => ['complaints']])
            ->getMock();
        $this->sut->shouldReceive('handleQuery')->once()->andReturnUsing(
            function ($dto) use ($mockComplaintResponse) {
                $this->assertSame(321, $dto->getLicence());
                return $mockComplaintResponse;
            }
        );

        $mockComplaintsHelperService = \Mockery::mock('\Common\Service\Helper\ComplaintsHelperService');
        $this->sm->setService('Helper\Complaints', $mockComplaintsHelperService);
        $mockComplaintsHelperService->shouldReceive('sortCasesOpenClosed')
            ->once()
            ->with(['complaints'])
            ->andReturn(['sorted-complaints']);

        $this->sut->shouldReceive('getTable')
            ->once()
            ->with('opposition-readonly', ['sorted-oppositions'])
            ->andReturn('TABLE HTML');
        $this->sut->shouldReceive('getTable')
            ->once()
            ->with('environmental-complaints-readonly', ['sorted-complaints'])
            ->andReturn('TABLE HTML');
        $this->sut->shouldReceive('renderView')
            ->once()
            ->andReturn('HTML');

        $this->sut->oppositionAction();
    }

    /**
     * @group application_controller
     */
    public function testOppositionActionOppositionError()
    {
        $this->mockController(
            '\Olcs\Controller\Licence\LicenceController'
        );

        $this->sut->shouldReceive('params->fromRoute')
            ->once()
            ->with('licence', null)
            ->andReturn(321);

        $mockOppostionResponse = \Mockery::mock()
            ->shouldReceive('isOk')->with()->once()->andReturn(false)
            ->getMock();
        $this->sut->shouldReceive('handleQuery')->once()->andReturn($mockOppostionResponse);

        $this->setExpectedException(\RuntimeException::class);

        $this->sut->oppositionAction();
    }

    /**
     * @group application_controller
     */
    public function testOppositionActionComplaintError()
    {
        $this->mockController(
            '\Olcs\Controller\Licence\LicenceController'
        );

        $this->sut->shouldReceive('params->fromRoute')
            ->once()
            ->with('licence', null)
            ->andReturn(321);

        $mockOppostionResponse = \Mockery::mock()
            ->shouldReceive('isOk')->with()->once()->andReturn(true)
            ->shouldReceive('getResult')->with()->once()->andReturn(['results' => ['oppositions']])
            ->getMock();
        $this->sut->shouldReceive('handleQuery')->once()->andReturn($mockOppostionResponse);

        $mockOppositionHelperService = \Mockery::mock('\Common\Service\Helper\OppositionHelperService');
        $this->sm->setService('Helper\Opposition', $mockOppositionHelperService);
        $mockOppositionHelperService->shouldReceive('sortOpenClosed')
            ->once()
            ->with(['oppositions'])
            ->andReturn(['sorted-oppositions']);

        $mockComplaintResponse = \Mockery::mock()
            ->shouldReceive('isOk')->with()->once()->andReturn(false)
            ->getMock();
        $this->sut->shouldReceive('handleQuery')->once()->andReturn($mockComplaintResponse);

        $this->setExpectedException(\RuntimeException::class);

        $this->sut->oppositionAction();
    }
}
