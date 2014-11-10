<?php

/**
 * Licence controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Licence;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Licence controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
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
     * @group licence_controller
     *
     * @dataProvider feesForLicenceProvider
     */
    public function testFeesAction($status, $feeStatus)
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

        $mockFeeService = $this->getMock('\StdClass', ['getFees']);
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
        $licenceData = array(
            'licNo' => 'TEST1234',
            'goodsOrPsv' => array(
                'id' => 'PSV',
                'description' => 'PSV'
            ),
            'licenceType' => array(
                'id' => 'L1',
                'description' => 'L1'
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
            'sort' => 'description'
        ];

        $altResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'description' => 'foo'
                ]
            ]
        ];

        $extendedListData = [
            'limit' => 100,
            'sort' => 'description',
            'order' => 'DESC',
            'page' => 1,
            'licenceId' => 1234
        ];

        $refDataList = [
            'limit' => 100,
            'sort' => 'description',
            'refDataCategoryId' => 'document_type'
        ];

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('DocumentSubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(9))
            ->method('makeRestCall')
            ->with('RefData', 'GET', $refDataList)
            ->will($this->returnValue($altResponse));

        $tableMock = $this->getMock('\stdClass', ['render']);
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

        $tableMock->expects($this->once())
            ->method('render');

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

    /**
     * @group licenceController
     */
    public function testDocumentsActionAjax()
    {
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
            'sort' => 'description'
        ];

        $altResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'description' => 'foo'
                ]
            ]
        ];

        $extendedListData = [
            'limit' => 100,
            'sort' => 'description',
            'order' => 'DESC',
            'page' => 1
        ];

        $refDataList = [
            'limit' => 100,
            'sort' => 'description',
            'refDataCategoryId' => 'document_type'
        ];

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('DocumentSubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(9))
            ->method('makeRestCall')
            ->with('RefData', 'GET', $refDataList)
            ->will($this->returnValue($altResponse));

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
     * Tests the bus action
     * @group licenceController
     */
    public function testBusAction()
    {
        $table = 'table';

        $licenceId = 110;
        $page = 1;
        $sort = 'regNo';
        $order = 'DESC';
        $limit = 10;

        $searchData['licence'] = $licenceId;
        $searchData['page'] = $page;
        $searchData['sort'] = $sort;
        $searchData['order'] = $order;
        $searchData['limit'] = $limit;

        $resultData = array();

        $this->controller->expects($this->at(0))
        ->method('getFromRoute')
        ->with('licence')
        ->will($this->returnValue($licenceId));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('BusReg'), $this->equalTo('GET'), $this->equalTo($searchData))
            ->will($this->returnValue($resultData));

        $form = $this->getMock('\stdClass', ['remove', 'setData']);

        $form->expects($this->once())
            ->method('remove')
            ->with('csrf');

        $form->expects($this->once())
            ->method('setData');

        $this->controller->expects($this->once())
            ->method('setTableFilters')
            ->with($form);

        $this->controller->expects($this->once())
            ->method('getForm')
            ->with('bus-reg-list')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('getTable')
            ->with(
                $this->equalTo('busreg'),
                $this->equalTo($resultData),
                $this->equalTo(
                    array_merge(
                        $searchData,
                        array('query' => $this->query)
                    )
                )
            )
            ->will($this->returnValue($table));

        $this->controller->busAction();
    }

    /**
     * @NOTE this test mirrors the controller which so far doesn't actually
     * care about the action; it always redirects to generate. Update the
     * name of it when/if it cares
     */
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
            ->will($this->returnValue('generate'));

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

        $response = $this->controller->documentsAction();
    }

    /**
     * @NOTE this test mirrors the controller which so far doesn't actually
     * care about the action; it always redirects to generate. Update the
     * name of it when/if it cares
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

        $response = $this->controller->documentsAction();
    }
}
