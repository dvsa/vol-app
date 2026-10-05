<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\TransportManager;

use Common\Form\Form;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\TransportManager\OperatorDeclarationController;

final class OperatorDeclarationControllerTest extends MockeryTestCase
{
    public function testItUsesTheOperatorDeclarationMarkup(): void
    {
        $translationHelper = m::mock(TranslationHelperService::class);
        $formHelper = m::mock(FormHelperService::class);
        $scriptFactory = m::mock(ScriptFactory::class);
        $request = m::mock(Request::class);
        $form = m::mock(Form::class);
        $layout = m::mock(ViewModel::class);
        $content = m::mock(ViewModel::class);

        $controller = m::mock(OperatorDeclarationController::class, [
            m::mock(NiTextTranslation::class),
            m::mock(AuthorizationService::class),
            $translationHelper,
            $formHelper,
            $scriptFactory,
            m::mock(AnnotationBuilder::class),
            m::mock(CommandService::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $property = new \ReflectionProperty(OperatorDeclarationController::class, 'tma');
        $property->setValue($controller, ['declaration' => 'TM DATABASE DECLARATION']);

        $request->shouldReceive('isPost')->once()->andReturnFalse();
        $controller->shouldReceive('getRequest')->twice()->andReturn($request);
        $controller->shouldReceive('getTmName')->once()->andReturn('Test TM');
        $controller->shouldReceive('getBackLink')->once()->andReturn('/back');
        $controller->shouldReceive('alterDeclarationForm')->once()->with($form);

        $selectedMarkup = null;
        $translationHelper->shouldReceive('translateReplace')
            ->once()
            ->withArgs(function (string $markup, array $replacements) use (&$selectedMarkup): bool {
                $selectedMarkup = $markup;

                return $replacements === [];
            })
            ->andReturn('OPERATOR DECLARATION');
        $formHelper->shouldReceive('createForm')
            ->once()
            ->with('TransportManagerApplicationDeclaration')
            ->andReturn($form);
        $formHelper->shouldReceive('setFormActionFromRequest')->once()->with($form, $request);
        $scriptFactory->shouldReceive('loadFiles')->once()->with(['tm-lva-declaration']);

        $controller->shouldReceive('getFlashMessenger->getContainer->offsetExists')
            ->once()
            ->with('govUkAccountError')
            ->andReturnFalse();
        $controller->shouldReceive('render')
            ->once()
            ->with(
                'transport-manager-application.declaration',
                $form,
                [
                    'content' => 'OPERATOR DECLARATION',
                    'tmFullName' => 'Test TM',
                    'backText' => 'common.link.back.label',
                    'backLink' => '/back',
                ]
            )
            ->andReturn($layout);
        $layout->shouldReceive('getChildrenByCaptureTo')->once()->with('content')->andReturn([$content]);
        $content->shouldReceive('setTemplate')->once()->with('pages/lva-tm-details-action');

        self::assertSame($layout, $controller->indexAction());
        self::assertSame('markup-tma-operator_declaration', $selectedMarkup);
    }
}
