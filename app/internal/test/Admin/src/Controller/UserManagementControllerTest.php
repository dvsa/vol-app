<?php

/**
 * Test UserManagementController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Mockery as m;
use Zend\Stdlib\ArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Test UserManagementController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class UserManagementControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function __construct()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
    }

    public function setUp()
    {
        $this->controller = new \Admin\Controller\UserManagementController();

    }

    public function testIndexAction()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'flashMessenger' => 'FlashMessenger', 'redirect' => 'Redirect',
                'viewHelperManager' => 'ViewHelperManager', 'script' => 'Script']
        );

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = $mockPluginManager->get('viewHelperManager', '');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        $mockSearchService = m::mock('Common\Service\Data\Search\Search');
        $mockSearchService->shouldReceive('setQuery')->with(m::type('object'))->andReturnSelf();
        $mockSearchService->shouldReceive('setRequest')->with(m::type('object'))->andReturnSelf();
        $mockSearchService->shouldReceive('setIndex')->with('user')->andReturnSelf();
        $mockSearchService->shouldReceive('setSearch')->with('*')->andReturnSelf();
        $mockScriptService = m::mock();
        $mockScriptService->shouldReceive('loadFiles')->with(m::type('array'));

        $userResults = [];

        $mockSearchService->shouldReceive('fetchResultsTable')->andReturn($userResults);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Search\Search')->andReturn($mockSearchService);
        $mockSl->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Script')->andReturn($mockScriptService);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromPost')->with('action')->andReturnNull();

        $mockFlash = $mockPluginManager->get('flashMessenger', '');
        $mockFlash->shouldReceive('addErrorMessage')->with('Please provide a search term');

        $mockContainer = new ArrayObject();
        $mockSearchForm = m::mock('Zend\Form\Form');
        $mockSearchForm->shouldReceive('getObject')->andReturn($mockContainer);
        $mockSearchForm->shouldReceive('setData');

        $placeholder->getContainer('headerSearch')->set($mockSearchForm);

        $this->controller->setPluginManager($mockPluginManager);
        $this->controller->setServiceLocator($mockSl);

        $this->controller->indexAction();

    }
}
