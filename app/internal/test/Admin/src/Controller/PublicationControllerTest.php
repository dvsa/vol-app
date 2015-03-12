<?php

/**
 * Test PublicationController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Controller;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Admin\Controller\PublicationController;

/**
 * Test PublicationController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationControllerTest extends MockeryTestCase
{
    public function setUp()
    {
        //used for testing index action
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

        //used for testing actions other than index action
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->sut = new PublicationController();
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


    public function testGenerateAction()
    {
        $publicationId = 10;
        $newPublicationId = $publicationId + 1;

        $mockPublication = m::mock('Common\Service\Data\Publication');
        $mockPublication->shouldReceive('generate')->with($publicationId)->andReturn($newPublicationId);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\Publication')
            ->andReturn($mockPublication);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger',
                'DataServiceManager' => 'DataServiceManager',
                'params' => 'Params'
            ]
        );

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('publication')->andReturn($publicationId);

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            null,
            ['action'=>'index', 'publication' => null],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->generateAction());
    }


    public function testPublishAction()
    {
        $publicationId = 10;

        $mockPublication = m::mock('Common\Service\Data\Publication');
        $mockPublication->shouldReceive('publish')->with($publicationId)->andReturn($publicationId);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\Publication')
            ->andReturn($mockPublication);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger',
                'DataServiceManager' => 'DataServiceManager',
                'params' => 'Params'
            ]
        );

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('publication')->andReturn($publicationId);

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            null,
            ['action'=>'index', 'publication' => null],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->publishAction());
    }


    /**
     * @dataProvider serviceAndResourceNotFoundProvider
     *
     * @param $expectedException
     * @param $message
     */
    public function testGenerateAndPublishActionExceptions(
        $expectedException,
        $message,
        $serviceMethod,
        $controllerAction
    ) {
        $publication = 99;

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'DataServiceManager' => 'DataServiceManager',
            ]
        );

        //publication link service
        $mockPublication = m::mock('Common\Service\Data\Publication');

        //publication should throw correct exception
        $class = 'Common\Exception\\' . $expectedException;
        $mockPublication->shouldReceive($serviceMethod)->andThrow(new $class($message));

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\Publication')
            ->andReturn($mockPublication);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('publication')->andReturn($publication);

        //flash messenger
        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addErrorMessage')->with($message)->once();

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->$controllerAction());
    }

    /**
     * Data provider for testGenerateAndPublishActionExceptions()
     *
     * @return array
     */
    public function serviceAndResourceNotFoundProvider()
    {
        return [
            [
                'DataServiceException',
                'Error message',
                'publish',
                'publishAction'
            ],
            [
                'ResourceNotFoundException',
                'Error message',
                'publish',
                'publishAction'
            ],
            [
                'DataServiceException',
                'Error message',
                'generate',
                'generateAction'
            ],
            [
                'ResourceNotFoundException',
                'Error message',
                'generate',
                'generateAction'
            ],

        ];
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
