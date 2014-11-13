<?php

namespace OlcsTest\Mvc\Controller\Plugin;

use Mockery as m;

/**
 * Class ComfirmPluginTest
 * @package OlcsTest\Mvc\Controller\Plugin
 */
class ConfirmTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function testInvokeGenerateForm()
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();

        $mockForm = m::mock('Zend\Form\Form');

        $controller = m::mock('\Olcs\Controller\Cases\Submission\SubmissionController[getForm]');
        $controller->shouldReceive('getForm')->with('Confirm')->andReturn($mockForm);
        $plugin->setController($controller);
        $result = $plugin->__invoke('some message');

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
    }

    public function testInvokeProcessForm()
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();

        $mockForm = m::mock('Zend\Form\Form');
        $mockForm->shouldReceive('setData')->withAnyArgs()->andReturn($mockForm);
        $mockForm->shouldReceive('isValid')->andReturn(true);

        $mockParams = m::mock('\StdClass[fromPost]');
        $mockParams->shouldReceive('fromPost')->andReturn([]);

        $mockRequest = m::mock('Zend\Http\Request');
        $mockRequest->shouldReceive('isPost')->andReturn(true);

        $controller = m::mock('\Olcs\Controller\Cases\Submission\SubmissionController[getForm, getRequest, params]');
        $controller->shouldReceive('getForm')->with('Confirm')->andReturn($mockForm);
        $controller->shouldReceive('getRequest')->andReturn($mockRequest);
        $controller->shouldReceive('params')->andReturn($mockParams);

        $plugin->setController($controller);
        $result = $plugin->__invoke('some message');

        $this->assertTrue($result);

    }

    public function testInvokeProcessInvalidForm()
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();

        $mockForm = m::mock('Zend\Form\Form');
        $mockForm->shouldReceive('setData')->withAnyArgs()->andReturn($mockForm);
        $mockForm->shouldReceive('isValid')->andReturn(false);

        $mockParams = m::mock('\StdClass[fromPost]');
        $mockParams->shouldReceive('fromPost')->andReturn([]);

        $mockRequest = m::mock('Zend\Http\Request');
        $mockRequest->shouldReceive('isPost')->andReturn(true);

        $controller = m::mock('\Olcs\Controller\Cases\Submission\SubmissionController[getForm, getRequest, params]');
        $controller->shouldReceive('getForm')->with('Confirm')->andReturn($mockForm);
        $controller->shouldReceive('getRequest')->andReturn($mockRequest);
        $controller->shouldReceive('params')->andReturn($mockParams);

        $plugin->setController($controller);
        $result = $plugin->__invoke('some message');

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
    }
}
