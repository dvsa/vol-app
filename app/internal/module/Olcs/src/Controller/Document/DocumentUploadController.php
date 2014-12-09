<?php

/**
 * Document Upload Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

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
    /**
     * how to map route param types to category IDs (see category db table)
     */
    public function getCategoryForType($type)
    {
        switch ($type) {
            case 'licence':
            case 'application':
                // @TODO - there are no subcategories defined for application yet!
                //return 9
            case 'case':
            case 'busReg':
                return 1;
            default:
                return null;
        }
    }

    public function getRouteParamKeyForType($type)
    {
        switch ($type) {
            // busReg routing is set up completely differently to everything else :(
            case 'busReg':
                return 'busRegId';
            case 'licence':
            case 'application':
            case 'case':
            case 'busReg':
            default:
                return $type;
        }
    }

    public function uploadAction()
    {
        $type = $this->params()->fromRoute('type');
        $category = $this->getCategoryForType($type);
        $this->getServiceLocator()
             ->get('DataServiceManager')
             ->get('Olcs\Service\Data\DocumentSubCategory')
             ->setCategory($category);

        $defaults = ['details' => ['category' => $category]];
        $form = $this->generateFormWithData('upload-document', 'processUpload', $defaults);

        $this->loadScripts(['upload-document']);

        $view = new ViewModel(['form' => $form]);

        $view->setTemplate('form-simple');
        return $this->renderView($view, 'Upload document');
    }

    public function processUpload($data)
    {
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        $files = $this->getRequest()->getFiles()->toArray();
        $files=$files['details'];

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

        // we don't know what params are needed to satisfy this type's
        // finalise route; so to be safe we supply them all
        $routeParams = array_merge(
            $routeParams,
            [
                'tmpId' => $file->getIdentifier()
            ]
        );

        // AC specifies this timestamp format...
        $fileName = date('YmdHi')
            . '_' . $this->formatFilename($files['file']['name'])
            . '.' . $file->getExtension();
        $data = [
            'identifier'          => $file->getIdentifier(),
            'description'         => $data['details']['description'],
            'filename'            => $fileName,
            'fileExtension'       => 'doc_' . $file->getExtension(),
            'category'            => $data['details']['category'],
            'documentSubCategory' => $data['details']['documentSubCategory'],
            'isDigital'           => true,
            'isReadOnly'          => true,
            'issuedDate'          => date('Y-m-d H:i:s'),
            'size'                => $file->getSize()
        ];

        $key = $this->getRouteParamKeyForType($type);
        $data[$type] = $routeParams[$key];

        // we need to link certain documents to multiple IDs
        switch ($type) {
            case 'application':
                $data['licence'] = $this->getLicenceIdForApplication();
                break;
            case 'case':
                $data['licence'] = $this->getLicenceIdForCase();
                break;
            case 'busReg':
                $data['licence'] = $this->getFromRoute('licence');
                break;
            default:
                break;
        }

        $this->makeRestCall(
            'Document',
            'POST',
            $data
        );

        $this->removeTmpData();

        return $this->redirectToDocumentRoute($type, null, $routeParams);
    }
}
