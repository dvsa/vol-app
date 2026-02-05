<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Traits\GenericUpload;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use DateTimeImmutable;
use DateTimeInterface;
use Dvsa\Olcs\Transfer\Query\Application\UploadEvidence;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Abstract Upload Evidence Controller
 */
abstract class AbstractUploadEvidenceController extends AbstractController
{
    use GenericUpload;
    use ApplicationControllerTrait;

    protected string $location = 'external';

    protected $operatingCentreId;

    /**
     * Data from API
     * @var array
     */
    private $data;

    /**
     * @var array|null
     */
    private $application;
    protected FileUploadHelperService $uploadHelper;
    protected string $startTime;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FileUploadHelperService $uploadHelper
     * @param TranslationHelperService $translationHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        FileUploadHelperService $uploadHelper,
        protected TranslationHelperService $translationHelper
    ) {
        $this->uploadHelper = $uploadHelper;
        $this->startTime = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
        );
    }

    /**
     * Index action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     * @psalm-suppress UndefinedDocblockClass
     */
    #[\Override]
    public function indexAction()
    {
        $form = $this->getForm();
        $form->get('correlationId')->setValue($this->startTime);

        $request = $this->getRequest();
        if ($request->isPost() && $request->getPost('saveAndContinue') !== null) {
            $form->setData((array) $request->getPost());

            if ($form->isValid()) {
                $dtoData = array_merge(
                    \Common\Data\Mapper\Lva\UploadEvidence::mapFromForm($form->getData()),
                    ['id' => $this->getIdentifier()]
                );
                $dtoData['financialEvidence'] = $this->shouldShowFinancialEvidence();
                $result = $this->handleCommand(
                    \Dvsa\Olcs\Transfer\Command\Application\UploadEvidence::create($dtoData)
                );
                if ($result->isOk()) {
                    if ($this->hasEvidenceBeenUploaded($form->getData())) {
                        $message = $this->translationHelper->translate('lva-financial-evidence-upload-now.success');
                        $this->addSuccessMessage($message);
                    }
                    return $this->redirect()->toRoute(
                        'lva-' . $this->lva . '/submission-summary',
                        ['application' => $this->getIdentifier()]
                    );
                }
                $this->addErrorMessage('unknown-error');
            }
        }

        $variables = [
            'warningText' => 'supply-supporting-evidence-warning'
        ];

        return $this->render('upload-evidence', $form, $variables);
    }

    /**
     * Get the form
     *
     * @return Form
     */
    private function getForm()
    {
        /** @var Form $form */
        $form = $this->formHelper->createForm('Lva\UploadEvidence');

        if ($this->shouldShowFinancialEvidence()) {
            $this->processFiles(
                $form,
                'financialEvidence->files',
                $this->financialEvidenceProcessFileUpload(...),
                $this->deleteFile(...),
                $this->financialEvidenceLoadFileUpload(...)
            );
        } else {
            $form->remove('financialEvidence');
        }

        if ($this->shouldShowOperatingCentre()) {
            $data = $this->getData();
            $form->get('operatingCentres')->setCount(count($data['operatingCentres']));
            \Common\Data\Mapper\Lva\UploadEvidence::mapFromResultForm($data, $form);

            for ($i = 0; $i < count($data['operatingCentres']); $i++) {
                // process files for each operating centre
                $this->operatingCentreId = $data['operatingCentres'][$i]['operatingCentre']['id'];
                $this->processFiles(
                    $form,
                    'operatingCentres->' . (string)$i . '->file',
                    $this->operatingCentreProcessFileUpload(...),
                    $this->deleteFile(...),
                    $this->operatingCentreLoadFileUpload(...)
                );
            }
        } elseif ($form->has('operatingCentres')) {
            $form->remove('operatingCentres');
        }

        if ($this->shouldShowSupportingEvidence()) {
            $this->processFiles(
                $form,
                'supportingEvidence->files',
                $this->supportingEvidenceProcessFileUpload(...),
                $this->deleteFile(...),
                $this->supportingEvidenceLoadFileUpload(...)
            );
        } elseif ($form->has('supportingEvidence')) {
            $form->remove('supportingEvidence');
        }

        return $form;
    }

    /**
     * Get list of uploaded document for an operating centre
     *
     * @return array
     */
    public function operatingCentreLoadFileUpload()
    {
        $startDateTime = new DateTimeImmutable($this->getRequest()->getPost('correlationId', $this->startTime));
        $data = $this->getData();
        $currentApplicationId = (int)$this->getIdentifier();
        foreach ($data['operatingCentres'] as $aocData) {
            if ($aocData['operatingCentre']['id'] === $this->operatingCentreId) {
                return array_filter(
                    $aocData['operatingCentre']['adDocuments'],
                    fn($document) => $document['isPostSubmissionUpload']
                        && isset($document['application']['id']) && $document['application']['id'] === $currentApplicationId
                        && (new DateTimeImmutable($document['createdOn'])) > $startDateTime,
                );
            }
        }

        return [];
    }

    /**
     * Process a file upload to an operating centre
     *
     * @param array $file Uploaded file data
     *
     * @return void
     */
    public function operatingCentreProcessFileUpload($file)
    {
        $data = [
            'description' => 'Advertisement',
            'category' => \Common\Category::CATEGORY_APPLICATION,
            'subCategory' => \Common\Category::DOC_SUB_CATEGORY_APPLICATION_ADVERT_DIGITAL,
            'isExternal'  => $this->isExternal(),
            'licence' => $this->getLicenceId(),
            'application' => $this->getIdentifier(),
            'operatingCentre' => $this->operatingCentreId,
            'isPostSubmissionUpload' => true,
        ];

        $this->uploadFile($file, $data);

        // force refresh of data
        $this->getData(true);
    }

    /**
     * Get financial evidence data
     *
     * @param bool $forceLoad Force load of data, eg don't use the cached version
     *
     * @return array
     */
    private function getFinancialEvidenceData($forceLoad = false)
    {
        $data = $this->getData($forceLoad);
        return $data['financialEvidence'];
    }

    /**
     * Get API data
     *
     * @param bool $forceLoad Force load of data, eg don't use the cached version
     *
     * @return array
     */
    private function getData($forceLoad = false)
    {
        if ($this->data !== null && !$forceLoad) {
            return $this->data;
        }

        $response = $this->handleQuery(UploadEvidence::create(['id' => $this->getIdentifier()]));
        if (!$response->isOk()) {
            throw new \RuntimeException('Error calling query UploadEvidence');
        }
        $this->data = $response->getResult();

        return $this->data;
    }

    /**
     * Should the Operating centre section be shown on the form
     *
     * @return bool
     */
    private function shouldShowOperatingCentre()
    {
        if (is_null($this->application)) {
            $this->application = $this->getApplicationData($this->getIdentifier());
        }
        return $this->application['goodsOrPsv']['id'] == 'lcat_psv' ? false : true;
    }

    /**
     * Should the financial evidence section be shown on the form
     *
     * @return bool
     */
    private function shouldShowFinancialEvidence()
    {
        $financialEvidenceData = $this->getFinancialEvidenceData();
        return $financialEvidenceData['canAdd'] === true;
    }

    /**
     * Process/upload a new financial evidence document
     *
     * @param array $file Data for the uploaded file
     *
     * @return void
     */
    public function financialEvidenceProcessFileUpload($file)
    {
        $applicationData = $this->getApplicationData($this->getIdentifier());
        $data = [
            'application' => $applicationData['id'],
            'description' => $file['name'],
            'category'    => \Common\Category::CATEGORY_APPLICATION,
            'subCategory' => \Common\Category::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL,
            'licence'     => $applicationData['licence']['id'],
            'isExternal'  => true,
            'isPostSubmissionUpload' => true,
        ];

        $this->uploadFile($file, $data);

        // force refresh of data
        $this->getFinancialEvidenceData(true);
    }

    /** Get list of financial evidence documents */
    public function financialEvidenceLoadFileUpload(): array
    {
        $startDateTime = new DateTimeImmutable($this->getRequest()->getPost('correlationId', $this->startTime));
        return array_filter(
            $this->getFinancialEvidenceData()['documents'],
            fn($doc) => $doc['isPostSubmissionUpload'] && (new DateTimeImmutable($doc['createdOn'])) > $startDateTime,
        );
    }

    /**
     * Process/upload a new supporting evidence document
     *
     * @param array $file Data for the uploaded file
     *
     * @return void
     */
    public function supportingEvidenceProcessFileUpload($file)
    {
        $applicationData = $this->getApplicationData($this->getIdentifier());
        $data = [
            'application' => $applicationData['id'],
            'description' => $file['name'],
            'category'    => \Common\Category::CATEGORY_APPLICATION,
            'subCategory' => \Common\Category::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE,
            'licence'     => $applicationData['licence']['id'],
            'isExternal'  => true,
            'isPostSubmissionUpload' => true,
        ];

        $this->uploadFile($file, $data);

        // force refresh of data
        $this->getData(true);
    }

    /** Get list of supporting evidence documents */
    public function supportingEvidenceLoadFileUpload(): array
    {
        $startDateTime = new DateTimeImmutable($this->getRequest()->getPost('correlationId', $this->startTime));
        return array_filter(
            $this->getData()['supportingEvidence'],
            fn($doc) => $doc['isPostSubmissionUpload'] && (new DateTimeImmutable($doc['createdOn'])) > $startDateTime,
        );
    }

    /**
     * Should we show the supporting evidence form?
     *
     * @return bool
     */
    private function shouldShowSupportingEvidence()
    {
        if (is_null($this->application)) {
            $this->application = $this->getApplicationData($this->getIdentifier());
        }
        return $this->application['status']['id'] === RefData::APPLICATION_STATUS_UNDER_CONSIDERATION;
    }

    private function hasEvidenceBeenUploaded(array $data): bool
    {
        // Check if "financialEvidence" exists and file list is not empty
        if (!empty($data['financialEvidence']['files']['list'])) {
            return true;
        }

        // We don't have a file upload list, so check each operating centre for "adPlacedIn" value.
        // Form validation will have ensured this is field is present when a file is uploaded
        if (!empty($data['operatingCentres'])) {
            foreach ($data['operatingCentres'] as $operatingCentre) {
                if (!empty($operatingCentre['adPlacedIn'])) {
                    return true;
                }
            }
        }

        // Check if "supportingEvidence" exists and file list is not empty
        if (!empty($data['supportingEvidence']['files']['list'])) {
            return true;
        }

        // If all conditions fail, return false
        return false;
    }
}
