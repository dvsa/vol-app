<?php

/**
 * Public Inquiry Test Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use \Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Public Inquiry Test Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class PublicInquiryControllerTest extends AbstractHttpControllerTestCase
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
        $this->sut = new \Olcs\Controller\Cases\PublicInquiry\PublicInquiryController();

        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../../' . 'config/application.config.php'
        );

        parent::setUp();
    }

    public function testRedirectToIndex()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['redirect' => 'Redirect']);
        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with('case_pi',
            ['action'=>'details'],
            ['code' => '303'], true)->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }

}
