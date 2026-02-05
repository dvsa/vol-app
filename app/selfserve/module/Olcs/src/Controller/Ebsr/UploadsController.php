<?php

namespace Olcs\Controller\Ebsr;

use Common\Category;
use Common\Controller\Lva\AbstractController;
use Common\Controller\Traits\GenericMethods;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\QueuePacks as QueuePacksCmd;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\EbsrSubmission as EbsrSubmissionQry;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\OrganisationUnprocessedList;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Request as HttpRequest;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class UploadsController
 */
class UploadsController extends AbstractController
{
    use GenericMethods;
    use FlashMessengerTrait;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        protected FileUploadHelperService $uploadHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Returns an EBSR submission details page
     *
     * @return ViewModel
     */
    public function detailAction()
    {
        $ebsrSubmission = $this->getEbsrSubmission();

        return new ViewModel(['ebsrSubmission' => $ebsrSubmission]);
    }

    /**
     * Uploads EBSR packs and optionally queues for processing
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function uploadAction()
    {
        /** @var HttpRequest $request */
        $request = $this->getRequest();

        /** @var \Common\Form\Form $form */
        $form = $this->formHelper
            ->createFormWithRequest('EbsrPackUpload', $request);

        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            $form->setData($postData);
        }

        // handle files
        $hasProcessedFiles = $this->processFiles(
            $form,
            'fields->files',
            $this->processEbsrFileUpload(...),
            $this->deleteFile(...),
            $this->getUploadedPacks(...),
            'fields->uploadedFileCount'
        );

        //if we have processed files, attempt to submit them for processing
        if (!$hasProcessedFiles && $request->isPost() && $form->isValid()) {
            $cmdData = ['submissionType' => $postData['fields']['submissionType']];
            $response = $this->handleCommand(QueuePacksCmd::create($cmdData));

            if ($response->isOk()) {
                $this->addSuccessMessage('ebsr-upload-success');
                return $this->redirect()->toRoute('bus-registration');
            }

            $this->addErrorMessage('ebsr-upload-fail');
        }

        return new ViewModel(['form' => $form]);
    }

    /**
     * Uploads the EBSR file
     *
     * @param array $file file being uploaded
     *
     * @return void
     * @throws \Common\Exception\File\InvalidMimeException
     * @throws \Exception
     */
    public function processEbsrFileUpload($file)
    {
        $dtoData = [
            'category' => Category::CATEGORY_BUS_REGISTRATION,
            'subCategory' => Category::BUS_SUB_CATEGORY_TRANSXCHANGE_FILE,
            'description' => $file['name'],
            'isExternal' => true,
            'isEbsrPack' => true
        ];

        $this->uploadFile($file, $dtoData);
    }

    /**
     * Gets a list of uploaded packs which have yet to be submitted
     *
     * @return array
     */
    public function getUploadedPacks()
    {
        $response = $this->handleQuery(OrganisationUnprocessedList::create([]));

        return $response->getResult();
    }

    /**
     * Gets the EBSR submission
     *
     * @return array
     */
    public function getEbsrSubmission()
    {
        $response = $this->handleQuery(EbsrSubmissionQry::create(['id' => $this->params()->fromRoute('id')]));

        if (!$response->isOk()) {
            return $this->notFoundAction();
        }

        return $response->getResult();
    }
}
