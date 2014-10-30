<?php

/**
 * Revoke controller test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\Controller\Cases\Processing\RevokeController;

/**
 * Revoke controller test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class RevokeControllerTest extends AbstractHttpControllerTestCase
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
        $this->sut = new RevokeController();

        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../../' . 'config/application.config.php'
        );

        parent::setUp();
    }

    public function testIndexAction()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['redirect' => 'Redirect']);
        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            null,
            ['action' => 'details'],
            [],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->indexAction());
    }
}
