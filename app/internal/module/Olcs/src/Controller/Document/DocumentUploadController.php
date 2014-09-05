<?php

namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;

use Dvsa\Jackrabbit\Data\Object\File;

class DocumentUploadController extends DocumentController
{
    private $tmpData = [];

    private function getTmpPath()
    {
        return self::TMP_STORAGE_PATH . '/' . $this->params()->fromRoute('tmpId');
    }

    private function fetchTmpData()
    {
        if (empty($this->tmpData)) {
            $path = $this->getTmpPath();
            $meta = $this->getContentStore()
                ->readMeta($path);

            $key = 'meta:' . self::METADATA_KEY;

            $this->tmpData = json_decode(
                $meta['metadataProperties'][$key],
                true
            );
        }
        return $this->tmpData;
    }

    public function finaliseAction()
    {
        $data = $this->fetchTmpData();

        $url = sprintf(
            '<a href="%s">Download</a>',
            $this->url()->fromRoute(
                'fetch_tmp_document',
                ['path' => $this->params()->fromRoute('tmpId')]
            )
        );

        // @TODO collapse into a loop?
        $category = $this->makeRestCall(
            'Category',
            'GET',
            ['id' => $data['details']['category']],
            ['properties' => ['description']]
        );
        $subCategory = $this->makeRestCall(
            'DocumentSubCategory',
            'GET',
            ['id' => $data['details']['documentSubCategory']],
            ['properties' => ['description']]
        );
        $template = $this->makeRestCall(
            'DocTemplate',
            'GET',
            ['id' => $data['details']['documentTemplate']],
            ['properties' => ['description']]
        );

        $data = [
            'category'    => $category['description'],
            'subCategory' => $subCategory['description'],
            'template'    => $template['description'],
            'link'        => $url
        ];
        $form = $this->generateFormWithData(
            'finalise-document',
            'processUpload',
            $data
        );

        $view = new ViewModel(['form' => $form]);
        // @TODO obviously, don't re-use this template; make a generic one if appropriate
        $view->setTemplate('task/add-or-edit');
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
        $fileExt = 'rtf';
        $fileName = str_replace(
            ' ',
            '_',
            date('YmdHi') . '_' . $templateName . '.' . $fileExt
        );

        $data = [
            'identifier'          => $filename,
            'description'         => $templateName,
            'licence'             => $this->params()->fromRoute('licence'),
            'filename'            => $fileName,
            'fileExtension'       => strtoupper($fileExt),
            'category'            => $data['details']['category'],
            'documentSubCategory' => $data['details']['documentSubCategory']
        ];

        $this->makeRestCall(
            'Document',
            'POST',
            $data
        );

        $uploader->remove($this->getTmpPath());

        // @TODO hardcoding the return URL isn't appropriate here; we may well
        // generate docs from a non licencing section (do we? Need to check)
        return $this->redirect()->toRoute(
            'licence/documents',
            ['licence' => $this->params()->fromRoute('licence')]
        );
    }
}
