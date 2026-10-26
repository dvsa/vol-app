<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\AbstractFinancialEvidenceController;
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
        $request = m::mock(Request::class);
        $request->allows('isPost')->andReturnFalse();

        $form = m::mock(Form::class);
        $form->allows('setData')->andReturnSelf();

        $formService = m::mock();
        $formService->allows('getForm')->andReturn($form);

        $formServiceManager = m::mock(FormServiceManager::class);
        $formServiceManager->allows('get')->with('lva-application-financial_evidence')->andReturn($formService);

        $scriptFactory = m::mock(ScriptFactory::class);
        $scriptFactory->allows('loadFiles');

        $lvaAdapter = m::mock();
        $lvaAdapter->allows('alterFormForLva');
        $lvaAdapter->allows('getData')->andReturn([
            'id' => 7,
            'version' => 1,
            'applicationReference' => 'OB1',
            'financialEvidenceUploaded' => null,
            'financialEvidence' => ['requiredFinance' => 7000],
        ]);

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

        $sut->allows('getRequest')->andReturn($request);
        $sut->allows('getIdentifier')->andReturn('7');
        $sut->allows('alterFormForLva');
        $sut->expects('processFiles')->andReturnFalse();
        $sut->expects('render')
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
