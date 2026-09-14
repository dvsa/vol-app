<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Adapters\ApplicationKnowledgeExperienceAdapter;
use Common\Data\Mapper\Lva\KnowledgeExperience;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\Application\UpdateKnowledgeExperience;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

final class KnowledgeExperienceController extends AbstractController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';

    protected string $location = 'internal';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected RestrictionHelperService $restrictionHelper,
        protected StringHelperService $stringHelper,
        protected ApplicationKnowledgeExperienceAdapter $lvaAdapter,
        protected FileUploadHelperService $uploadHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService
        );
    }

    #[\Override]
    public function indexAction()
    {
        $request = $this->getRequest();
        $id = $this->getIdentifier();

        if ($request->isPost()) {
            $formData = KnowledgeExperience::mapFromPost(
                $request->getPost()->getArrayCopy()
            );
        } else {
            $formData = KnowledgeExperience::mapFromResult(
                $this->lvaAdapter->getData((int) $id)
            );
        }

        $form = $this->formServiceManager
            ->get('lva-application-knowledge_experience')
            ->getForm($request)
            ->setData($formData);

        $hasProcessedFiles = $this->processFiles(
            $form,
            'evidence->files',
            function (array $file) use ($id): void {
                $data = $this->lvaAdapter->getUploadMetaData(
                    $file,
                    (int) $id
                );

                $data['isExternal'] = $this->isExternal();

                $this->uploadFile($file, $data);

                $this->lvaAdapter->getData((int) $id, true);
            },
            fn(int $documentId): bool => $this->deleteFile($documentId),
            fn(): array => $this->lvaAdapter->getDocuments((int) $id),
            'evidence->uploadedFileCount'
        );

        if (
            !$hasProcessedFiles
            && $request->isPost()
            && $form->isValid()
        ) {
            $data = KnowledgeExperience::mapFromForm($formData);
            $data['id'] = $id;

            $response = $this->handleCommand(
                UpdateKnowledgeExperience::create($data)
            );

            if ($response->isOk()) {
                return $this->completeSection('knowledge_experience');
            }

            $this->flashMessengerHelper
                ->addCurrentErrorMessage('unknown-error');
        }

        $this->scriptFactory->loadFile('financial-evidence');

        return $this->render(
            'knowledge_experience',
            $form
        );
    }
}