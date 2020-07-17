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
     * @dataProvider dpTestInvokeGenerateForm
     */
    public function testInvokeGenerateForm($confirmLabel, $cancelLabel, $defaultLabelParams)
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();

        $mockFormCustomLabels = m::mock('Zend\Form\Form')
            ->shouldReceive('getAttribute')
            ->with('action')
            ->twice()
            ->andReturn('action')
            ->shouldReceive('setAttribute')
            ->with('action', 'action?foo=bar')
            ->shouldReceive('get')
            ->with('custom')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValue')
            ->with('custom')
            ->shouldReceive('get')
            ->with('form-actions')
            ->twice()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('confirm')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setLabel')
            ->with($confirmLabel)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('cancel')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setLabel')
            ->with($cancelLabel)
            ->once()
            ->getMock();

        $controller = m::mock('\Olcs\Controller\Cases\Submission\SubmissionController[getForm]');
        $controller
            ->shouldReceive('getForm')
            ->with('Confirm')
            ->andReturn($mockFormCustomLabels)
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
        if ($defaultLabelParams) {
            $result = $plugin->__invoke('some message', true, 'custom');
        } else {
            $result = $plugin->__invoke('some message', true, 'custom', $confirmLabel, $cancelLabel);
        }

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
    }

    public function dpTestInvokeGenerateForm()
    {
        return [
            ['Continue', 'Cancel', true],
            ['customConfirm', 'customCancel', false],
        ];
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
        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->twice()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('confirm')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setLabel')
            ->with('Continue')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('cancel')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setLabel')
            ->with('Cancel')
            ->once();

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
