<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Lva;

use Common\Controller\Lva\AbstractKnowledgeExperienceController;
use Common\Controller\Lva\Adapters\ApplicationKnowledgeExperienceAdapter;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\Application\UpdateKnowledgeExperience;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(AbstractKnowledgeExperienceController::class)]
final class AbstractKnowledgeExperienceControllerTest extends MockeryTestCase
{
    private const APPLICATION_ID = 7;

    private m\MockInterface $sut;

    private m\MockInterface $request;

    private m\MockInterface $form;

    private m\MockInterface $lvaAdapter;

    private m\MockInterface $flashMessengerHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->request = m::mock(Request::class);
        $this->form = m::mock(Form::class);
        $this->lvaAdapter = m::mock(ApplicationKnowledgeExperienceAdapter::class);
        $this->flashMessengerHelper = m::mock(FlashMessengerHelperService::class);

        $formService = m::mock();
        $formService->expects('getForm')->with($this->request)->andReturn($this->form);

        $formServiceManager = m::mock(FormServiceManager::class);
        $formServiceManager->expects('get')->with('lva-application-knowledge_experience')->andReturn($formService);

        $scriptFactory = m::mock(ScriptFactory::class);
        $scriptFactory->allows('loadFile')->with('financial-evidence');

        $this->sut = m::mock(
            AbstractKnowledgeExperienceController::class,
            [
                m::mock(NiTextTranslation::class),
                m::mock(AuthorizationService::class),
                $this->flashMessengerHelper,
                $formServiceManager,
                $scriptFactory,
                $this->lvaAdapter,
                m::mock(FileUploadHelperService::class),
            ]
        )->makePartial()->shouldAllowMockingProtectedMethods();

        $this->sut->allows('getRequest')->andReturn($this->request);
        $this->sut->allows('getIdentifier')->andReturn((string) self::APPLICATION_ID);
    }

    public function testIndexActionGetPopulatesFormFromSavedAnswers(): void
    {
        $this->request->allows('isPost')->andReturnFalse();

        $this->lvaAdapter->expects('getData')->with(self::APPLICATION_ID)->andReturn([
            'id' => self::APPLICATION_ID,
            'version' => 3,
            'knowledgeExperienceEvidenceUploaded' => RefData::AD_UPLOAD_NOW,
            'knowledgeExperienceOlat' => 'N',
        ]);

        $this->form->expects('setData')->with([
            'id' => self::APPLICATION_ID,
            'version' => 3,
            'evidence' => [
                'uploadNowRadio' => RefData::AD_UPLOAD_NOW,
                'uploadLaterRadio' => null,
            ],
            'knowledgeExperienceOlat' => 'N',
        ])->andReturnSelf();

        $this->sut->expects('processFiles')->andReturnFalse();
        $this->sut->expects('handleCommand')->never();
        $this->sut->expects('render')->with('knowledge_experience', $this->form)->andReturn('VIEW');

        $this->assertSame('VIEW', $this->sut->indexAction());
    }

    public function testIndexActionValidPostSavesAndCompletesSection(): void
    {
        $this->givenPost([
            'id' => self::APPLICATION_ID,
            'version' => 3,
            'evidence' => ['uploadNow' => (string) RefData::AD_UPLOAD_LATER],
            'knowledgeExperienceOlat' => 'Y',
        ]);

        $this->form->expects('setData')->andReturnSelf();
        $this->form->expects('isValid')->andReturnTrue();

        $this->sut->expects('processFiles')->andReturnFalse();

        $response = m::mock(Response::class);
        $response->expects('isOk')->andReturnTrue();

        $this->sut->expects('handleCommand')
            ->with(m::on(
                fn(UpdateKnowledgeExperience $command): bool => $command->getArrayCopy() === [
                    'id' => self::APPLICATION_ID,
                    'version' => 3,
                    'evidenceUploadType' => RefData::AD_UPLOAD_LATER,
                    'knowledgeExperienceOlat' => 'Y',
                ]
            ))
            ->andReturn($response);

        $this->sut->expects('completeSection')->with('knowledge_experience')->andReturn('REDIRECT');

        $this->assertSame('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionPostThatOnlyHandlesFilesDoesNotSave(): void
    {
        $this->givenPost([
            'id' => self::APPLICATION_ID,
            'version' => 3,
            'evidence' => ['uploadNow' => (string) RefData::AD_UPLOAD_NOW, 'files' => ['list' => []]],
        ]);

        $this->form->expects('setData')->andReturnSelf();
        $this->form->expects('isValid')->never();

        $this->sut->expects('processFiles')
            ->with($this->form, 'evidence->files', m::type(\Closure::class), m::type(\Closure::class), m::type(\Closure::class), 'evidence->uploadedFileCount')
            ->andReturnTrue();
        $this->sut->expects('handleCommand')->never();
        $this->sut->expects('render')->with('knowledge_experience', $this->form)->andReturn('VIEW');

        $this->assertSame('VIEW', $this->sut->indexAction());
    }

    public function testIndexActionFailedSaveShowsErrorAndRerenders(): void
    {
        $this->givenPost([
            'id' => self::APPLICATION_ID,
            'version' => 3,
            'evidence' => [],
        ]);

        $this->form->expects('setData')->andReturnSelf();
        $this->form->expects('isValid')->andReturnTrue();

        $this->sut->expects('processFiles')->andReturnFalse();

        $response = m::mock(Response::class);
        $response->expects('isOk')->andReturnFalse();
        $this->sut->expects('handleCommand')->andReturn($response);

        $this->flashMessengerHelper->expects('addCurrentErrorMessage')->with('unknown-error');
        $this->sut->expects('render')->with('knowledge_experience', $this->form)->andReturn('VIEW');

        $this->assertSame('VIEW', $this->sut->indexAction());
    }

    private function givenPost(array $post): void
    {
        $this->request->allows('isPost')->andReturnTrue();
        $this->request->allows('getPost')->andReturn(new Parameters($post));
    }
}
