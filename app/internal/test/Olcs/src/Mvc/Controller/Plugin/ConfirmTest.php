<?php

namespace OlcsTest\Mvc\Controller\Plugin;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class ComfirmPluginTest
 * @package OlcsTest\Mvc\Controller\Plugin
 */
class ConfirmTest extends TestCase
{
    protected $sut;

    /**
     * @group confirmPlugin
     */
    public function testInvokeGenerateForm()
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();

        $mockForm = m::mock('Zend\Form\Form')
            ->shouldReceive('getAttribute')
            ->with('action')
            ->andReturn('action')
            ->shouldReceive('setAttribute')
            ->with('action', 'action?foo=bar')
            ->shouldReceive('get')
            ->andReturn(
                m::mock()
                ->shouldReceive('setValue')
                ->with('custom')
                ->getMock()
            )
            ->getMock();

        $controller = m::mock('\Olcs\Controller\Cases\Submission\SubmissionController[getForm]');
        $controller
            ->shouldReceive('getForm')
            ->with('Confirm')
            ->andReturn($mockForm)
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromPost')
                ->andReturn([])
                ->shouldReceive('fromQuery')
                ->andReturn(['foo' => 'bar'])
                ->getMock()
            );

        $plugin->setController($controller);
        $result = $plugin->__invoke('some message', true, 'custom');

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
    }

    /**
     * @group confirmPlugin
     */
    public function testInvokeProcessForm()
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();

        $mockForm = m::mock('Zend\Form\Form');
        $mockForm->shouldReceive('setData')->withAnyArgs()->andReturn($mockForm);
        $mockForm->shouldReceive('isValid')->andReturn(true);
        $mockForm
            ->shouldReceive('get')
            ->andReturn(
                m::mock()
                ->shouldReceive('setValue')
                ->with('custom')
                ->getMock()
            );

        $mockParams = m::mock('\StdClass[fromPost]');
        $mockParams
            ->shouldReceive('fromPost')
            ->andReturn(['form-actions' => ['confirm' => 'confirm']])
            ->shouldReceive('fromQuery')
            ->andReturn([])
            ->getMock();

        $mockRequest = m::mock('Zend\Http\Request');
        $mockRequest->shouldReceive('isPost')->andReturn(true);

        $controller = m::mock('\Olcs\Controller\Cases\Submission\SubmissionController[getForm, getRequest, params]');
        $controller->shouldReceive('getForm')->with('Confirm')->andReturn($mockForm);
        $controller->shouldReceive('getRequest')->andReturn($mockRequest);
        $controller->shouldReceive('params')->andReturn($mockParams);
        $controller->shouldReceive('setTerminal')->andReturn(true);

        $plugin->setController($controller);
        $result = $plugin->__invoke('some message', true, 'custom');

        $this->assertTrue($result);
    }

    /**
     * @group confirmPlugin
     */
    public function testInvokeProcessInvalidForm()
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();

        $mockForm = m::mock('Zend\Form\Form');
        $mockForm->shouldReceive('setData')->withAnyArgs()->andReturn($mockForm);
        $mockForm->shouldReceive('isValid')->andReturn(false);

        $mockParams = m::mock('\StdClass[fromPost]');
        $mockParams
            ->shouldReceive('fromPost')
            ->andReturn(['form-actions' => ['confirm' => 'confirm']])
            ->shouldReceive('fromQuery')
            ->andReturn([])
            ->getMock();

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
