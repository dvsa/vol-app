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
        if ($this->isButtonPressed('cancel')) {
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
            'documentSubCategory' => $data['details']['documentSubCategory'],
            'isDigital'           => true,
            'isReadOnly'          => true,
            'size'                => 0  // @TODO fetch from $file
        ];

        $this->makeRestCall(
            'Document',
            'POST',
            $data
        );

        $uploader->remove($this->getTmpPath());

        return $this->redirect()->toRoute(
            $this->params()->fromRoute('type').'/documents',
            $this->params()->toArray()
        );
    }
}
