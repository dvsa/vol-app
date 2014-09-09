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
                'getTable',
                'getLicence',
                'getRequest',
                'getForm',
                'loadScripts',
                'getFromRoute',
                'params',
                'redirect',
                'getServiceLocator',
                'buildTable',
                'url'
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

        $this->controller->expects($this->any())
             ->method('getServiceLocator')
             ->will($this->returnValue($this->getServiceLocatorTranslator()));

        parent::setUp();
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

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->with('DocumentSearchView', 'GET', $expectedParams)
            ->will($this->returnValue([]));

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

    public function testDocumentsActionAjax()
    {

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

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->will($this->returnValue($response));

        $this->request->expects($this->once())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));

        $view = $this->controller->documentsAction();

        $this->assertTrue($view->terminate());
    }

    public function testBusAction()
    {
        $table = 'table';

        $licenceId = 110;
        $page = 1;
        $sort = 'regNo';
        $order = 'desc';
        $limit = 10;
        $url = 'url';

        $searchData['licence'] = $licenceId;
        $searchData['page'] = $page;
        $searchData['sort'] = $sort;
        $searchData['order'] = $order;
        $searchData['limit'] = $limit;
        $searchData['url'] = $url;

        $resultData = array();

        $this->controller->expects($this->at(0))
        ->method('getFromRoute')
        ->with('licence')
        ->will($this->returnValue($licenceId));

        $this->controller->expects($this->at(1))
            ->method('getFromRoute')
            ->with($this->equalTo('page'), $this->equalTo($page))
            ->will($this->returnValue($page));

        $this->controller->expects($this->at(2))
            ->method('getFromRoute')
            ->with($this->equalTo('sort'), $this->equalTo($sort))
            ->will($this->returnValue($sort));

        $this->controller->expects($this->at(3))
            ->method('getFromRoute')
            ->with($this->equalTo('order'), $this->equalTo($order))
            ->will($this->returnValue($order));

        $this->controller->expects($this->at(4))
            ->method('getFromRoute')
            ->with($this->equalTo('limit'), $this->equalTo($limit))
            ->will($this->returnValue($limit));

        $this->controller->expects($this->once())
            ->method('url')
            ->will($this->returnValue($url));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('BusReg'), $this->equalTo('GET'), $this->equalTo($searchData))
            ->will($this->returnValue($resultData));

        $this->controller->expects($this->once())
            ->method('buildTable')
            ->with($this->equalTo('busreg'), $this->equalTo($resultData), $this->equalTo($searchData))
            ->will($this->returnValue($table));

        $this->controller->busAction();
    }
}
