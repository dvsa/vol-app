<?php

/**
 * Test PublicationController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Test PublicationController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->controller = $this->getMock(
            '\Admin\Controller\PublicationController',
            [
                'getView', 'renderView',
                'getRequest', 'getQuery', 'getPost',
                'isXmlHttpRequest',
                'getViewHelperManager', 'get', 'getContainer', 'append', 'set', 'prepend',
                'makeRestCall', 'getServiceLocator',
                'buildTable', 'alterTable',
                'getQueryOrRouteParam',
                'getListVars'
            ]
        );

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setTemplate'
            ]
        );
    }

    public function testIndexAction()
    {
        $this->controller->expects($this->any())->method('getRequest')->will($this->returnSelf());
        $this->controller->expects($this->any())->method('getQuery')->will($this->returnValue(null));
        $this->controller->expects($this->any())->method('getPost')->will($this->returnValue(null));
        $this->controller->expects($this->any())->method('isXmlHttpRequest')->will($this->returnValue(false));

        $this->controller->expects($this->any())->method('getViewHelperManager')->will($this->returnSelf());
        $this->controller->expects($this->any())->method('getServiceLocator')->will($this->returnSelf());
        $this->controller->expects($this->any())->method('get')->will($this->returnSelf());
        $this->controller->expects($this->any())->method('getContainer')->will($this->returnSelf());
        $this->controller->expects($this->any())->method('append')->will($this->returnSelf());
        $this->controller->expects($this->any())->method('set')->will($this->returnSelf());
        $this->controller->expects($this->any())->method('prepend')->will($this->returnSelf());

        // Test
        $this->controller->expects($this->any())->method('append')
            ->with($this->equalTo('Publications'))->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->controller->expects($this->once())
            ->method('renderView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('pages/table-comments');

        $this->controller->setInlineScripts([]);

        $this->assertSame($this->view, $this->controller->indexAction());
    }

    /*public function testGetTableParams()
    {
        $c = $this->controller;

        $params = [
            'page'    => 1,
            'sort'    => 'id',
            'order'   => 'DESC',
            'limit'   => 10,
            'query'   => null,
        ];

        $c->expects($this->any())->method('getRequest')->will($this->returnSelf());
        $c->expects($this->any())->method('getQuery')->will($this->returnValue(null));
        $c->expects($this->any())->method('getPost')->will($this->returnValue(null));
        $c->expects($this->any())->method('isXmlHttpRequest')->will($this->returnValue(false));

        $c->expects($this->any())->method('getListVars')->will($this->returnValue([]));

        $c->expects($this->once())->method('getQueryOrRouteParam')->with('page', 1)->will($this->returnValue(1));
        $c->expects($this->once())->method('getQueryOrRouteParam')->with('sort', 'id')->will($this->returnValue('id'));
        $c->expects($this->once())->method('getQueryOrRouteParam')->with('order', 'DESC')
            ->will($this->returnValue('DESC'));
        $c->expects($this->once())->method('getQueryOrRouteParam')->with('limit', 10)->will($this->returnValue(10));

        $c->expects($this->once())->method('getQueryOrRouteParam')->with('pubStatus', 'pub_s_new')
            ->will($this->returnValue('pub_s_new'));

        $this->assertEquals(
            array_merge($params, ['pubStatus' => 'pub_s_new']),
            $c->getTableParams()
        );
    }*/
}
