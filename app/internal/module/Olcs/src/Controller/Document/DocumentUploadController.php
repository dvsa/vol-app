<?php

namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;

use Dvsa\Jackrabbit\Data\Object\File;

class DocumentUploadController extends DocumentController
{
    public function finaliseAction()
    {
        if ($this->isButtonPressed('back')) {
            return $this->redirect()->toRoute(
                $this->params('type').'/documents/generate',
                $this->params()->fromRoute()
            );
        }
        $data = $this->fetchTmpData();

        $entities = [
            'Category' => 'category',
            'DocumentSubCategory' => 'documentSubCategory',
            'DocTemplate' => 'documentTemplate'
        ];

        $lookups = [];
        foreach ($entities as $entity => $key) {
            $result = $this->makeRestCall(
                $entity,
                'GET',
                ['id' => $data['details'][$key]],
                ['properties' => ['description']]
            );
            $lookups[$key] = $result['description'];
        }

        $url = sprintf(
            '<a href="%s">%s</a>',
            $this->url()->fromRoute(
                'fetch_tmp_document',
                ['path' => $this->params()->fromRoute('tmpId')]
            ),
            $lookups['documentTemplate']
        );

        $data = [
            'category'    => $lookups['category'],
            'subCategory' => $lookups['documentSubCategory'],
            'template'    => $url
        ];
        $form = $this->generateFormWithData(
            'finalise-document',
            'processUpload',
            $data
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form-simple');
        return $this->renderView($view, 'Amend letter');
    }


    public function processUpload($data)
    {
        $data = $this->fetchTmpData();

        // @TODO wrap this in more abstract methods if poss. Also need
        // to sort out proper validation; look at FormActionController
        // and see if we can modify that
        $files = $this->getRequest()->getFiles()->toArray();

        $uploader = $this->getUploader();
        $uploader->setFile($files['file']);
        $filename = $uploader->upload(self::FULL_STORAGE_PATH);

        // @TODO DRY up with previous method
        $template = $this->makeRestCall(
            'DocTemplate',
            'GET',
            ['id' => $data['details']['documentTemplate']],
            ['properties' => ['description']]
        );

        $templateName = $template['description'];
        $fileName = date('YmdHi') . '_' . $this->formatFilename($templateName) . '.rtf';

        $data = [
            'identifier'          => $filename,
            'description'         => $templateName,
            'licence'             => $this->params('licence'),
            'filename'            => $fileName,
            'fileExtension'       => 'doc_rtf',
            'category'            => $data['details']['category'],
            'documentSubCategory' => $data['details']['documentSubCategory'],
            'isDigital'           => true,
            'isReadOnly'          => true,
            'issuedDate'          => date('Y-m-d H:i:s'),
            'size'                => 0  // @TODO fetch from $file
        ];

        $this->makeRestCall(
            'Document',
            'POST',
            $data
        );

        $uploader->remove($this->getTmpPath());

        return $this->redirect()->toRoute(
            $this->params('type') . '/documents',
            $this->params()->fromRoute()
        );
    }
}
