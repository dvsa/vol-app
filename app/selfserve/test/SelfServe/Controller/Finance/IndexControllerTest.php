<?php
/**
 * Tests for Finance page
 * @author		Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */


namespace SelfServe\test\Finance;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;

class IndexControllerTest extends AbstractControllerTestCase
{
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        parent::setUp();

    }


    /**
     * Test access to operator location step
     */
    public function testFinanceIndex()
    {
        $this->dispatch('/selfserve/finance');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('selfserve');
        $this->assertControllerName('selfserve\finance\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('selfserve/finance');

    }

}