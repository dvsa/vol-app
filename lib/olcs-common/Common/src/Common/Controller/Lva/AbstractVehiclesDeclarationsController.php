<?php

declare(strict_types=1);

namespace Common\Controller\Lva;

use Common\Category;
use Common\Controller\Traits\GenericUpload;
use Common\Data\Mapper\Lva\PsvSmallEvidence;
use Common\Data\Mapper\Lva\PsvLargeEvidence;
use Common\Data\Mapper\Lva\PsvMainOccupationUndertakings;
use Common\Data\Mapper\Lva\PsvOperateLarge;
use Common\Data\Mapper\Lva\PsvOperateNovelty;
use Common\Data\Mapper\Lva\PsvOperateSmall;
use Common\Data\Mapper\Lva\PsvSmallConditions;
use Common\Data\Mapper\Lva\PsvWrittenExplanation;
use Common\Data\Mapper\Lva\VehicleSize;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\Application\UpdateMainOccupationEvidence;
use Dvsa\Olcs\Transfer\Command\Application\UpdateMainOccupationUndertakings;
use Dvsa\Olcs\Transfer\Command\Application\UpdateNoveltyVehicles;
use Dvsa\Olcs\Transfer\Command\Application\UpdateSmallVehicleConditionsAndUndertaking;
use Dvsa\Olcs\Transfer\Command\Application\UpdateSmallVehicleEvidence;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleNinePassengers;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleOperatingSmall;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleSize;
use Dvsa\Olcs\Transfer\Command\Application\UpdateWrittenExplanation;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\Application\Documents;
use Dvsa\Olcs\Transfer\Query\Application\VehicleDeclaration;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

abstract class AbstractVehiclesDeclarationsController extends AbstractController
{
    use GenericUpload;

    private const SECTION_VEHICLES_SIZE = 'vehicles_size';
    private const SECTION_PSV_OPERATE_LARGE = 'psv_operate_large';
    private const SECTION_PSV_OPERATE_SMALL = 'psv_operate_small';
    private const SECTION_SMALL_CONDITIONS = 'psv_small_conditions';
    private const SECTION_PSV_OPERATE_NOVELTY = 'psv_operate_novelty';
    private const SECTION_PSV_SMALL_PART_WRITTEN = 'psv_small_part_written';
    private const SECTION_PSV_DOCUMENTARY_EVIDENCE_SMALL = 'psv_documentary_evidence_small';
    private const PSV_DOCUMENTARY_EVIDENCE_LARGE = 'psv_documentary_evidence_large';
    private const SECTION_PSV_MAIN_OCCUPATION_UNDERTAKINGS = 'psv_main_occupation_undertakings';

    private array $documents = [];
    private int $uploadCategory = Category::CATEGORY_APPLICATION;
    private int $uploadSubCategory;
    private array $data = [];

    private array $mapperClasses = [
        self::SECTION_VEHICLES_SIZE => VehicleSize::class,
        self::SECTION_PSV_OPERATE_LARGE => PsvOperateLarge::class,
        self::SECTION_PSV_OPERATE_SMALL => PsvOperateSmall::class,
        self::SECTION_PSV_SMALL_PART_WRITTEN => PsvWrittenExplanation::class,
        self::SECTION_SMALL_CONDITIONS => PsvSmallConditions::class,
        self::SECTION_PSV_OPERATE_NOVELTY => PsvOperateNovelty::class,
        self::SECTION_PSV_DOCUMENTARY_EVIDENCE_SMALL => PsvSmallEvidence::class,
        self::PSV_DOCUMENTARY_EVIDENCE_LARGE => PsvLargeEvidence::class,
        self::SECTION_PSV_MAIN_OCCUPATION_UNDERTAKINGS => PsvMainOccupationUndertakings::class,
    ];

    private array $updateCommands = [
        self::SECTION_VEHICLES_SIZE => UpdateVehicleSize::class,
        self::SECTION_PSV_OPERATE_LARGE => UpdateVehicleNinePassengers::class,
        self::SECTION_PSV_OPERATE_SMALL => UpdateVehicleOperatingSmall::class,
        self::SECTION_PSV_SMALL_PART_WRITTEN => UpdateWrittenExplanation::class,
        self::SECTION_SMALL_CONDITIONS => UpdateSmallVehicleConditionsAndUndertaking::class,
        self::SECTION_PSV_OPERATE_NOVELTY => UpdateNoveltyVehicles::class,
        self::SECTION_PSV_DOCUMENTARY_EVIDENCE_SMALL => UpdateSmallVehicleEvidence::class,
        self::PSV_DOCUMENTARY_EVIDENCE_LARGE => UpdateMainOccupationEvidence::class,
        self::SECTION_PSV_MAIN_OCCUPATION_UNDERTAKINGS => UpdateMainOccupationUndertakings::class,
    ];

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected FileUploadHelperService $uploadHelper,
        protected FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    private function handleSection(string $section): Response|ViewModel
    {
        $request = $this->getRequest();
        $isPost = $request->isPost();

        $data = $isPost ? $request->getPost()->getArrayCopy() : $this->fetchFormData($section);

        /** @var Form $form */
        $form = $this->formServiceManager->get('lva-' . $this->lva . '-vehicles_declarations_' . $section)->getForm();
        $this->alterFormForSection($section, $form, $data);
        $form->setData($data);

        if ($isPost && $form->isValid() && $this->saveSection($section, $data)) {
            return $this->completeSection($section);
        }

        return $this->render($section, $form);
    }

    private function alterFormForSection(string $section, Form $form, array $data): Form
    {
        switch ($section) {
            case self::SECTION_SMALL_CONDITIONS:
                if ($data['isOperatingSmallPsvAsPartOfLarge'] === true) {
                    $this->formHelper->remove($form, 'psvSmallVhlConditions');
                }
                break;
        }

        return $form;
    }

    private function saveSection(string $section, array $data): bool
    {
        $mapperClass = $this->getMapperForSection($section);
        $saveData = $mapperClass::mapFromForm($data);
        $saveData['id'] = $this->getApplicationId();

        $updateClass = $this->getUpdateClassForSection($section);

        /** @var CommandInterface $updateCmd */
        $updateCmd = $updateClass::create($saveData);
        $response = $this->handleCommand($updateCmd);

        if ($response->isOk()) {
            return true;
        }

        $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');
        return false;
    }

    private function fetchFormData(string $section): array
    {
        $mapperClass = $this->getMapperForSection($section);

        // Load data and map it to the form
        $data = $this->loadData();
        return $mapperClass::mapFromResult($data);
    }

    private function getMapperForSection(string $section): string
    {
        $mapperClass = $this->mapperClasses[$section] ?? null;

        if ($mapperClass === null) {
            throw new \RuntimeException('No mapper class found for section: ' . $section);
        }

        return $mapperClass;
    }

    private function getUpdateClassForSection(string $section): string
    {
        $updateClass = $this->updateCommands[$section] ?? null;

        if ($updateClass === null) {
            throw new \RuntimeException('No transfer object found to update section: ' . $section);
        }

        return $updateClass;
    }

    public function sizeAction(): Response|ViewModel
    {
        return $this->handleSection(self::SECTION_VEHICLES_SIZE);
    }

    public function operateLargeAction(): Response|ViewModel
    {
        return $this->handleSection(self::SECTION_PSV_OPERATE_LARGE);
    }

    public function noveltyAction(): Response|ViewModel
    {
        $this->scriptFactory->loadFile('vehicle-limo');
        return $this->handleSection(self::SECTION_PSV_OPERATE_NOVELTY);
    }

    public function operateSmallAction(): Response|ViewModel
    {
        return $this->handleSection(self::SECTION_PSV_OPERATE_SMALL);
    }

    public function smallConditionsAction(): Response|ViewModel
    {
        return $this->handleSection(self::SECTION_SMALL_CONDITIONS);
    }

    public function mainOccupationAction(): Response|ViewModel
    {
        return $this->handleSection(self::SECTION_PSV_MAIN_OCCUPATION_UNDERTAKINGS);
    }

    public function writtenExplanationAction(): Response|ViewModel
    {
        return $this->handleSection(self::SECTION_PSV_SMALL_PART_WRITTEN);
    }

    public function smallEvidenceAction(): Response|ViewModel
    {
        $this->uploadSubCategory = Category::DOC_SUB_CATEGORY_SMALL_PSV_EVIDENCE_DIGITAL;
        return $this->handleEvidenceSection(self::SECTION_PSV_DOCUMENTARY_EVIDENCE_SMALL);
    }

    public function largeEvidenceAction(): Response|ViewModel
    {
        $this->uploadSubCategory = Category::DOC_SUB_CATEGORY_LARGE_PSV_EVIDENCE_DIGITAL;
        return $this->handleEvidenceSection(self::PSV_DOCUMENTARY_EVIDENCE_LARGE);
    }

    private function handleEvidenceSection(string $section): Response|ViewModel
    {
        $request = $this->getRequest();
        $isPost = $request->isPost();

        if ($isPost) {
            $mapperClass = $this->getMapperForSection($section);
            $data = $mapperClass::mapFromPost($request->getPost()->getArrayCopy());
        } else {
            $data = $this->fetchFormData($section);
        }

        /** @var Form $form */
        $form = $this->formServiceManager->get('lva-' . $this->lva . '-vehicles_declarations_' . $section)->getForm($this->getRequest());
        $form->setData($data);

        $hasProcessedFiles = $this->processFiles(
            $form,
            'evidence->files',
            $this->processFileUpload(...),
            $this->deleteFile(...),
            $this->getDocuments(...),
            'evidence->uploadedFileCount'
        );

        // update application record and redirect
        if (!$hasProcessedFiles && $isPost && $form->isValid() && $this->saveSection($section, $data)) {
            return $this->completeSection($section);
        }

        // load scripts
        $this->scriptFactory->loadFile('financial-evidence');

        return $this->render($section, $form);
    }

    private function processFileUpload($file): void
    {
        $this->documents = [];

        $data = [
            'description' => $file['name'],
            'category' => $this->uploadCategory,
            'subCategory' => $this->uploadSubCategory,
            'isExternal' => $this->isExternal(),
            'application' => $this->getApplicationId(),
            'licence' => $this->getLicenceId(),
        ];

        $this->uploadFile($file, $data);
    }

    /**
     * Get documents relating to the application
     */
    private function getDocuments(): array
    {
        if (empty($this->documents)) {
            $params = [
                'id' => $this->getApplicationId(),
                'category' => $this->uploadCategory,
                'subCategory' => $this->uploadSubCategory,
            ];

            $response = $this->handleQuery(Documents::create($params));
            $this->documents = $response->getResult();
        }

        return $this->documents;
    }

    private function loadData(): array
    {
        if (empty($this->data)) {
            $response = $this->handleQuery(
                VehicleDeclaration::create(['id' => $this->getApplicationId()])
            );
            if (!$response->isOk()) {
                throw new \RuntimeException('Error getting vehicle declaration');
            }

            $this->data = $response->getResult();
        }

        return $this->data;
    }
}
