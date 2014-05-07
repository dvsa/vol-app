<?php

namespace OlcsTest\Controller\Dashboard;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

class IndexControllerTest extends AbstractHttpControllerTestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\Dashboard\IndexController',
            $methods
        );

        $this->controller->setServiceLocator($this->getApplicationServiceLocator());

    }

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        parent::setUp();

    }

    /**
     * @group Dashboard
     *
     */
    public function testIndexAction()
    {
        $this->setUpMockController( [
            'makeRestCall',
            'getPluginManager',
        ]);

        $urlPlugin = $this->getMock('\stdClass', array('fromRoute'));

        $mockPluginManager = $this->getMock('\stdClass', array('get'));
        $mockPluginManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($urlPlugin));

        $this->controller->expects($this->once())
            ->method('getPluginManager')
            ->will($this->returnValue($mockPluginManager));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array('Count' => 0, 'Results' => array())))
        ;

        $this->controller->indexAction();
    }


    /**
     * @group Dashboard
     *
     */
    public function testCreateApplicationAction()
    {
        $this->setUpMockController( [
            'makeRestCall',
            'redirect',
        ]);

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValue(array('id' => 1)))
        ;

        $redirectMock = $this->getMock('\stdClass', array('toRoute'));
        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirectMock))
        ;

        $this->controller->createApplicationAction();
    }


}
