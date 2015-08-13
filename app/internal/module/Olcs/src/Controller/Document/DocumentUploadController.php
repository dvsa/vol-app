<?php

/**
 * Document Upload Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Dvsa\Olcs\Transfer\Command\Document\CreateDocument;
use Zend\View\Model\ViewModel;
use Common\Service\File\Exception as FileException;

/**
 * Document Generation Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentUploadController extends AbstractDocumentController
{
    public function uploadAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();
            $category = $data['details']['category'];
        } else {
            $type = $this->params()->fromRoute('type');
            $category = $this->getCategoryForType($type);
            $data = [
                'details' => [
                    'category' => $category
                ]
            ];
        }

        // @todo data services for list data are changing as part of another story
        $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\DocumentSubCategory')
            ->setCategory($category);

        $form = $this->generateFormWithData('upload-document', 'processUpload', $data);

        $this->loadScripts(['upload-document']);

        $view = new ViewModel(['form' => $form]);

        $view->setTemplate('partials/form');
        return $this->renderView($view, 'Upload document');
    }

    public function processUpload($data)
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        $files = $this->getRequest()->getFiles()->toArray();
        $files = $files['details'];

        if (!isset($files['file']) || $files['file']['error'] !== UPLOAD_ERR_OK) {
            // @TODO this needs to be handled better; by the time we get here we
            // should *know* that our files are valid
            $this->addErrorMessage('Sorry; there was a problem uploading the file. Please try again.');
            return $this->redirectToDocumentRoute($type, 'upload', $routeParams);
        }

        $uploader = $this->getUploader();
        $uploader->setFile($files['file']);

        try {
            $file = $uploader->upload();
        } catch (FileException $ex) {
            $this->addErrorMessage('The document store is unavailable. Please try again later');
            return $this->redirectToDocumentRoute($type, 'upload', $routeParams);
        }

        $routeParams = array_merge(
            $routeParams,
            [
                'doc' => $file->getIdentifier()
            ]
        );

        $fileName = $this->getDocumentTimestamp()
            . '_' . $this->formatFilename($files['file']['name'])
            . '.' . $file->getExtension();

        $data = [
            'identifier'    => $file->getIdentifier(),
            'description'   => $data['details']['description'],
            'filename'      => $fileName,
            'category'      => $data['details']['category'],
            'subCategory'   => $data['details']['documentSubCategory'],
            'isExternal'    => false,
            'isReadOnly'    => true,
            'size'          => $file->getSize()
        ];

        $key = $this->getRouteParamKeyForType($type);
        $data[$type] = $routeParams[$key];

        // we need to link certain documents to multiple IDs
        switch ($type) {
            case 'application':
                $data['licence'] = $this->getLicenceIdForApplication();
                break;

            case 'case':
                $data = array_merge(
                    $data,
                    $this->getCaseData()
                );
                break;

            case 'busReg':
                $data['licence'] = $routeParams['licence'];
                break;

            default:
                break;
        }

        $response = $this->handleCommand(CreateDocument::create($data));

        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnkownError();
        }

        return $this->redirectToDocumentRoute($type, null, $routeParams);
    }
}
