<?php

declare(strict_types=1);

namespace OlcsTest\Mvc\Controller\Plugin;

use Common\Rbac\Service\Permission;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Navigation\Navigation;
use Laminas\View\Renderer\PhpRenderer as ViewRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\Cases\Submission\SubmissionController;
use Olcs\Mvc\Controller\Plugin\Confirm;
use Olcs\Service\Data\Submission;

final class ConfirmTest extends TestCase
{
    protected $sut;
    protected $configHelper;

    #[\Override]
    public function setUp(): void
    {

        $translationHelper = m::mock(TranslationHelperService::class);
        $formHelper = m::mock(FormHelperService::class);
        $flashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $navigation = m::mock(Navigation::class);
        $urlHelper = m::mock(UrlHelperService::class);
        $this->configHelper = [];
        $viewRenderer = m::mock(ViewRenderer::class);
        $submissionService = m::mock(Submission::class);
        $permissionService = m::mock(Permission::class);
        $uploadHelper = m::mock(FileUploadHelperService::class);
        $this->sut = m::mock(SubmissionController::class, [
            $translationHelper,
            $formHelper,
            $flashMessengerHelper,
            $navigation,
            $urlHelper,
            $this->configHelper,
            $viewRenderer,
            $submissionService,
            $permissionService,
            $uploadHelper
        ])->makePartial();
    }
    #[\PHPUnit\Framework\Attributes\Group('confirmPlugin')]
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestInvokeGenerateForm')]
    public function testInvokeGenerateForm(mixed $confirmLabel, mixed $cancelLabel, mixed $defaultLabelParams): void
    {
        $plugin = new Confirm();
        $this->configHelper = [];
        $mockFormCustomLabels = m::mock(\Laminas\Form\Form::class)
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

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $result);
    }

    public static function dpTestInvokeGenerateForm(): \Iterator
    {
        yield ['Continue', 'Cancel', true];
        yield ['customConfirm', 'customCancel', false];
    }

    #[\PHPUnit\Framework\Attributes\Group('confirmPlugin')]
    public function testInvokeProcessForm(): void
    {
        $plugin = new Confirm();

        $mockForm = m::mock(\Laminas\Form\Form::class);
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

        $mockRequest = m::mock(\Laminas\Http\Request::class);
        $mockRequest->shouldReceive('isPost')->andReturn(true);

        $this->sut->shouldReceive('getForm')->with('Confirm')->andReturn($mockForm);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);
        $this->sut->shouldReceive('params')->andReturn($mockParams);
        $this->sut->shouldReceive('setTerminal')->andReturn(true);

        $plugin->setController($this->sut);
        $result = $plugin->__invoke('some message', true, 'custom');

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Group('confirmPlugin')]
    public function testInvokeProcessInvalidForm(): void
    {
        $plugin = new Confirm();

        $mockForm = m::mock(\Laminas\Form\Form::class);
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

        $mockRequest = m::mock(\Laminas\Http\Request::class);
        $mockRequest->shouldReceive('isPost')->andReturn(true);

        $this->sut->shouldReceive('getForm')->with('Confirm')->andReturn($mockForm);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);
        $this->sut->shouldReceive('params')->andReturn($mockParams);

        $plugin->setController($this->sut);
        $result = $plugin->__invoke('some message');

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $result);
    }
}
