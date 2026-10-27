<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\AbstractFinancialEvidenceController;
use Common\Data\Mapper\Lva\FinancialEvidence;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\Http\Request;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(AbstractFinancialEvidenceController::class)]
final class AbstractFinancialEvidenceControllerTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpLocation')]
    public function testIndexActionPassesLocationToView(string $location): void
    {
        $lvaData = [
            'id' => 7,
            'version' => 1,
            'applicationReference' => 'OB1',
            'financialEvidenceUploaded' => null,
            'financialEvidence' => ['requiredFinance' => 7000],
        ];

        $request = m::mock(Request::class);
        $request->expects('isPost')->twice()->withNoArgs()->andReturnFalse();

        $form = m::mock(Form::class);
        $form->expects('setData')->once()->with(FinancialEvidence::mapFromResult($lvaData))->andReturnSelf();

        $formService = m::mock();
        $formService->expects('getForm')->once()->with($request)->andReturn($form);

        $formServiceManager = m::mock(FormServiceManager::class);
        $formServiceManager->expects('get')->once()->with('lva-application-financial_evidence')->andReturn($formService);

        $scriptFactory = m::mock(ScriptFactory::class);
        $scriptFactory->expects('loadFiles')->once()->with(['financial-evidence'])->andReturnNull();

        $lvaAdapter = m::mock();
        $lvaAdapter->expects('alterFormForLva')->once()->with($form)->andReturnNull();
        $lvaAdapter->expects('getData')->twice()->with('7')->andReturn($lvaData);

        $sut = m::mock(
            AbstractFinancialEvidenceController::class,
            [
                m::mock(NiTextTranslation::class),
                m::mock(AuthorizationService::class),
                m::mock(FlashMessengerHelperService::class),
                $formServiceManager,
                $scriptFactory,
                m::mock(TableFactory::class),
                m::mock(AnnotationBuilder::class),
                m::mock(CommandService::class),
                $lvaAdapter,
                m::mock(FileUploadHelperService::class),
            ]
        )->makePartial()->shouldAllowMockingProtectedMethods();

        (new \ReflectionProperty(AbstractController::class, 'location'))->setValue($sut, $location);
        (new \ReflectionProperty(AbstractController::class, 'lva'))->setValue($sut, 'application');

        $sut->expects('getRequest')->twice()->withNoArgs()->andReturn($request);
        $sut->expects('getIdentifier')->once()->withNoArgs()->andReturn('7');
        $sut->expects('alterFormForLva')->once()->with($form)->andReturnNull();
        $sut->expects('processFiles')
            ->once()
            ->with(
                $form,
                'evidence->files',
                m::type(\Closure::class),
                m::type(\Closure::class),
                m::type(\Closure::class),
                'evidence->uploadedFileCount'
            )
            ->andReturnFalse();
        $sut->expects('render')
            ->once()
            ->with('financial_evidence', $form, m::subset(['location' => $location, 'requiredFinance' => 7000]))
            ->andReturn('VIEW');

        $this->assertSame('VIEW', $sut->indexAction());
    }

    public static function dpLocation(): array
    {
        return [
            'internal' => [AbstractController::LOC_INTERNAL],
            'external' => [AbstractController::LOC_EXTERNAL],
        ];
    }
}
