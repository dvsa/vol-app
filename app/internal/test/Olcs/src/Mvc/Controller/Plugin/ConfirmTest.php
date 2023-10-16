<?php

namespace OlcsTest\Mvc\Controller\Plugin;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Navigation\Navigation;
use Laminas\View\Renderer\PhpRenderer as ViewRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\Cases\Submission\SubmissionController;
use Olcs\Service\Data\Submission;

/**
 * Class ComfirmPluginTest
 *
 * @package OlcsTest\Mvc\Controller\Plugin
 */
class ConfirmTest extends TestCase
{
    protected $sut;
    public function setUp(): void
    {

        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->formHelper = m::mock(FormHelperService::class);
        $this->flashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->navigation = m::mock(Navigation::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->configHelper = array();
        $this->viewRenderer = m::mock(ViewRenderer::class);
        $this->submissionService = m::mock(Submission::class);
        $this->sut = m::mock(SubmissionController::class, [
            $this->translationHelper,
            $this->formHelper,
            $this->flashMessengerHelper,
            $this->navigation,
            $this->urlHelper,
            $this->configHelper,
            $this->viewRenderer,
            $this->submissionService
        ])->makePartial();
    }
    /**
     * @group        confirmPlugin
     * @dataProvider dpTestInvokeGenerateForm
     */
    public function testInvokeGenerateForm($confirmLabel, $cancelLabel, $defaultLabelParams)
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();
        $this->configHelper = array();
        $mockFormCustomLabels = m::mock('Laminas\Form\Form')
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

        $this->sut
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

        $plugin->setController($this->sut);
        if ($defaultLabelParams) {
            $result = $plugin->__invoke('some message', true, 'custom');
        } else {
            $result = $plugin->__invoke('some message', true, 'custom', $confirmLabel, $cancelLabel);
        }

        $this->assertInstanceOf('\Laminas\View\Model\ViewModel', $result);
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

        $mockForm = m::mock('Laminas\Form\Form');
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

        $mockRequest = m::mock('Laminas\Http\Request');
        $mockRequest->shouldReceive('isPost')->andReturn(true);

        $this->sut->shouldReceive('getForm')->with('Confirm')->andReturn($mockForm);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);
        $this->sut->shouldReceive('params')->andReturn($mockParams);
        $this->sut->shouldReceive('setTerminal')->andReturn(true);

        $plugin->setController($this->sut);
        $result = $plugin->__invoke('some message', true, 'custom');

        $this->assertTrue($result);
    }

    /**
     * @group confirmPlugin
     */
    public function testInvokeProcessInvalidForm()
    {
        $plugin = new \Olcs\Mvc\Controller\Plugin\Confirm();

        $mockForm = m::mock('Laminas\Form\Form');
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

        $mockRequest = m::mock('Laminas\Http\Request');
        $mockRequest->shouldReceive('isPost')->andReturn(true);

        $this->sut->shouldReceive('getForm')->with('Confirm')->andReturn($mockForm);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);
        $this->sut->shouldReceive('params')->andReturn($mockParams);

        $plugin->setController($this->sut);
        $result = $plugin->__invoke('some message');

        $this->assertInstanceOf('\Laminas\View\Model\ViewModel', $result);
    }
}
