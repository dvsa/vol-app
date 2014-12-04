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
    private $categoryMap = [
        'licence'     => 1,
        //'application' => 9,
        'application' => 1, // @TODO - there are no subcategories defined for application yet!
    ];

    private $documentRouteMap = [
        'licence' => 'licence/documents',
        'application' => 'lva-application/documents',
    ];

    public function uploadAction()
    {
        $type = $this->params()->fromRoute('type');
        $category = $this->categoryMap[$type];
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
            $route = $this->documentRouteMap[$type].'/upload';
            return $this->redirect()->toRoute($route, $routeParams);
        }
        $uploader = $this->getUploader();
        $uploader->setFile($files['file']);

        try {
            $file = $uploader->upload();
        } catch (FileException $ex) {
            $this->addErrorMessage('The document store is unavailable. Please try again later');
            $route = $this->documentRouteMap[$type].'/upload';
            return $this->redirect()->toRoute($route, $routeParams);
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

        $data[$type] = $routeParams[$type];

        // we need to link certain documents to multiple IDs
        // ... this will be expanded for future stories
        switch ($type) {
            case 'application':
                $data['licence'] = $this->getServiceLocator()->get('Entity\Application')
                    ->getLicenceIdForApplication($routeParams[$type]);
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

        $route = $this->documentRouteMap[$type];
        return $this->redirect()->toRoute($route, $routeParams);
    }
}
