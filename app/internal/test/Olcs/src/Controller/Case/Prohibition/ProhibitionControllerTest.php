<?php

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\Prohibition\ProhibitionController;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;

/**
 * Class ProhibitionControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ProhibitionControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ControllerRouteMatchHelper
     */
    protected $routeMatchHelper;

    public function __construct()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
    }

    public function setUp()
    {
        $this->sut = new ProhibitionController();

        parent::setUp();
    }

    /**
     * Tests the details action
     */
    public function testDetailsAction()
    {
        $id = 1;
        $mockRestData = ['id' => $id];

        $expected = [
            'id' => $id,
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockRestData);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('prohibition')->andReturn($id);
        $this->sut->setPluginManager($mockPluginManager);

        //placeholders
        $placeholder = new \Zend\View\Helper\Placeholder();
        $placeholder->getContainer('prohibition')->set($expected);

        //add placeholders to view helper
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $this->sut->setServiceLocator($mockServiceManager);

        $data = $this->sut->detailsAction();

        $this->assertEquals($data, $expected);
    }

    public function testDetailsActionNotFound()
    {
        $id = null;
        $mockRestData = false;

        $expected = null;

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockRestData);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('prohibition')->andReturn($id);
        $this->sut->setPluginManager($mockPluginManager);

        $event = $this->routeMatchHelper->getMockRouteMatch(array('action' => 'not-found'));
        $this->sut->setEvent($event);

        //placeholders
        $placeholder = new \Zend\View\Helper\Placeholder();
        $placeholder->getContainer('prohibition')->set($expected);

        //add placeholders to view helper
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $this->sut->setServiceLocator($mockServiceManager);

        $data = $this->sut->detailsAction();

        $this->assertEquals($data, $expected);
    }
}
