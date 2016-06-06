<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\Traits\GenericMethods;
use Zend\Http\Request as HttpRequest;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\OrganisationUnprocessedList;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\QueuePacks as QueuePacksCmd;
use Zend\View\Model\ViewModel;
use Common\Util\FlashMessengerTrait;
use Common\Controller\Lva\AbstractController;

/**
 * Class UploadsController
 */
class UploadsController extends AbstractController
{
    use GenericMethods;
    use FlashMessengerTrait;

    /**
     * Uploads EBSR packs and optionally queues for processing
     *
     * @return ViewModel
     */
    public function uploadAction()
    {
        /** @var HttpRequest $request */
        $request = $this->getRequest();

        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createFormWithRequest('EbsrPackUpload', $request);

        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            $form->setData($postData);
        }

        // handle files
        $hasProcessedFiles = $this->processFiles(
            $form,
            'fields->files',
            array($this, 'processEbsrFileUpload'),
            array($this, 'deleteFile'),
            array($this, 'getUploadedPacks'),
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
     * @param array $file
     * @throws \Common\Exception\File\InvalidMimeException
     * @throws \Exception
     */
    public function processEbsrFileUpload($file)
    {
        $dtoData = [
            'category' => 3,
            'subCategory' => 36,
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
}
