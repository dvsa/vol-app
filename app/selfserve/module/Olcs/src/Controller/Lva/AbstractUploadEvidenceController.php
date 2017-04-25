<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Traits\GenericUpload;
use Common\Form\Form;
use Common\Service\Data\CategoryDataService;
use Dvsa\Olcs\Transfer\Query\Application\UploadEvidence;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Abstract Upload Evidence Controller
 */
abstract class AbstractUploadEvidenceController extends AbstractController
{
    use GenericUpload,
        ApplicationControllerTrait;

    protected $location = 'external';

    /**
     * Financial evidence data
     * @var array
     */
    private $financialEvidenceData;

    /**
     * Index action
     *
     * @return \Common\View\Model\Section
     */
    public function indexAction()
    {
        $form = $this->getForm();

        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('saveAndContinue') !== null) {

            $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Application\UploadEvidence::create(['id' => $this->getIdentifier()])
            );

            return $this->redirect()->toRoute(
                'lva-'. $this->lva .'/submission-summary',
                ['application' => $this->getIdentifier()]
            );
        }

        return $this->render('upload-evidence', $form);
    }

    /**
     * Get the form
     *
     * @return Form
     */
    private function getForm()
    {
        /** @var Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('Lva\UploadEvidence');
        /** @var \Common\Service\Helper\FormHelperService $formHelper */
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        if ($this->shouldShowFinancialEvidence()) {
            $this->processFiles(
                $form,
                'financialEvidence->files',
                [$this, 'processFileUpload'],
                [$this, 'deleteFile'],
                [$this, 'loadFileUpload']
            );
        } else {
            $formHelper->remove($form, 'financialEvidence');
        }

        return $form;
    }

    /**
     * Get financial evidence data
     *
     * @param bool $forceLoad Force load of data, eg don't use the cached version
     *
     * @return array|mixed
     */
    private function getFinancialEvidenceData($forceLoad = false)
    {
        if ($this->financialEvidenceData !== null && !$forceLoad) {
            return $this->financialEvidenceData;
        }

        $response = $this->handleQuery(UploadEvidence::create(['id' => $this->getIdentifier()]));
        if (!$response->isOk()) {
            throw new \RuntimeException('Error calling query UploadEvidence');
        }
        $this->financialEvidenceData = $response->getResult();

        return $this->financialEvidenceData;
    }

    /**
     * Should the financial evidence section be shown on the form
     *
     * @return bool
     */
    private function shouldShowFinancialEvidence()
    {
        $financialEvidenceData = $this->getFinancialEvidenceData();
        return $financialEvidenceData['financialEvidence']['canAdd'] === true;
    }

    /**
     * Process/upload a new financial evidence document
     *
     * @param array $file Data for the uploaded file
     *
     * @return void
     */
    public function processFileUpload($file)
    {
        $applicationData = $this->getApplicationData($this->getIdentifier());
        $data = [
            'application' => $applicationData['id'],
            'description' => $file['name'],
            'category'    => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL,
            'licence'     => $applicationData['licence']['id'],
            'isExternal'  => true,
        ];

        $this->uploadFile($file, $data);

        // force refresh of data
        $this->getFinancialEvidenceData(true);
    }

    /**
     * Get list of financial evidence documents
     *
     * @return array
     */
    public function loadFileUpload()
    {
        $financialEvidenceData = $this->getFinancialEvidenceData();
        return $financialEvidenceData['financialEvidence']['documents'];
    }
}
