<?php

/**
 * Bus Processing Registration History Controller Test
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace OlcsTest\Controller\Bus\Processing;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Olcs\TestHelpers\ControllerAddEditHelper;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Mockery as m;

/**
 * Bus Processing Controller Test
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class BusProcessingRegistrationHistoryControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/application.config.php'
        );
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $this->sut = new \Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryController();

        parent::setUp();
    }

    /**
     * unit test for index action
     */
    public function testIndexAction()
    {
        $this->assertTrue(true);
    }
}
