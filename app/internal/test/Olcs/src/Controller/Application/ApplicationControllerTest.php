<?php

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Application;

use PHPUnit_Framework_TestCase;
use OlcsTest\Bootstrap;

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTest extends PHPUnit_Framework_TestCase
{
    private $sut;
    private $sm;
    private $mockParams;
    private $mockRouteParams;
    private $pluginManager;

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sm->setAllowOverride(true);

        $this->sut = $this->getMock('\Olcs\Controller\Application\ApplicationController', array('render'));
        $this->sut->setServiceLocator($this->sm);
        $this->pluginManager = $this->sut->getPluginManager();
    }

    /**
     * @group application_controller
     */
    public function testCaseAction()
    {
        $this->mockRender();

        $view = $this->sut->caseAction();

        $this->assertEquals('application/index', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testEnvironmentalAction()
    {
        $this->mockRender();

        $view = $this->sut->environmentalAction();

        $this->assertEquals('application/index', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testDocumentAction()
    {
        $this->mockRender();

        $view = $this->sut->documentAction();

        $this->assertEquals('application/index', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testProcessingAction()
    {
        $this->mockRender();

        $view = $this->sut->processingAction();

        $this->assertEquals('application/index', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithGet()
    {
        $id = 7;

        $this->mockRouteParam('application', $id);

        $this->mockRender();

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $formHelper = $this->getMock('\stdClass', ['createForm']);
        $formHelper->expects($this->once())
            ->method('createForm')
            ->with('GenericConfirmation')
            ->will($this->returnValue('FORM'));
        $this->sm->setService('Helper\Form', $formHelper);

        $view = $this->sut->grantAction();
        $this->assertEquals('application/grant', $view->getTemplate());
        $this->assertEquals('FORM', $view->getVariable('form'));
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithCancelButton()
    {
        $id = 7;
        $post = array(
            'form-actions' => array(
                'cancel' => 'foo'
            )
        );

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->grantAction());
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithPost()
    {
        $id = 7;
        $post = array();

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        // @todo Mock grant action post
        $this->fail('Finish me');

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->grantAction());
    }

    /**
     * Helper method
     */
    protected function mockRouteParam($name, $value)
    {
        $this->mockRouteParams[$name] = $value;

        if ($this->mockParams === null) {
            $this->mockParams = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', array('__invoke'));

            $this->mockParams->expects($this->any())
                ->method('__invoke')
                ->will($this->returnCallback(array($this, 'getRouteParam')));

            $this->pluginManager->setService('params', $this->mockParams);
        }
    }

    /**
     * Helper method
     */
    public function getRouteParam($name)
    {
        return isset($this->mockRouteParams[$name]) ? $this->mockRouteParams[$name] : null;
    }

    /**
     * Helper method
     */
    protected function mockRender()
    {
        $this->sut->expects($this->once())
            ->method('render')
            ->will(
                $this->returnCallback(
                    function ($view) {
                        return $view;
                    }
                )
            );
    }

    /**
     * Helper method
     */
    protected function mockRedirect()
    {
        $mockRedirect = $this->getMock('\Zend\Mvc\Controller\Plugin\Redirect', array('toRoute'));
        $this->pluginManager->setService('Redirect', $mockRedirect);
        return $mockRedirect;
    }
}
