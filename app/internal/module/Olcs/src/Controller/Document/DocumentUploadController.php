<?php

/**
 * Document Upload Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;

/**
 * Document Upload Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentUploadController extends AbstractDocumentController
{
    private $mimeTypeMap = [
        'application/rtf' => [
            'ref_data' => 'doc_rtf',
            'extension' => 'rtf'
        ]
    ];

    public function finaliseAction()
    {
        $routeParams = $this->params()->fromRoute();
        if ($this->isButtonPressed('back')) {
            return $this->redirect()->toRoute(
                $routeParams['type'] . '/documents/generate',
                $routeParams
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

        $templateName = $lookups['documentTemplate'];

        $url = sprintf(
            '<a href="%s">%s</a>',
            $this->url()->fromRoute(
                'fetch_tmp_document',
                [
                    'id' => $routeParams['tmpId'],
                    'filename' => $this->formatFilename($templateName) . '.rtf'
                ]
            ),
            $templateName
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
        $routeParams = $this->params()->fromRoute();
        $type = $routeParams['type'];

        $data = $this->fetchTmpData();

        $files = $this->getRequest()->getFiles()->toArray();

        if (!isset($files['file']) || $files['file']['error'] !== UPLOAD_ERR_OK) {
            // @TODO this needs to be handled better; by the time we get here we
            // should *know* that our files are valid
            return $this->redirect()->toRoute(
                $routeParams['type'] . '/documents/finalise',
                $routeParams
            );
        }

        $uploader = $this->getUploader();
        $uploader->setFile($files['file']);
        $file = $uploader->upload();

        $template = $this->makeRestCall(
            'DocTemplate',
            'GET',
            ['id' => $data['details']['documentTemplate']],
            ['properties' => ['description']]
        );

        $templateName = $template['description'];

        // AC specifies this timestamp format...
        $fileName = date('YmdHi')
            .  '_' . $this->formatFilename($templateName)
            . '.' . $this->getExtensionMap($file->getType());

        $data = [
            'identifier'          => $file->getIdentifier(),
            'description'         => $templateName,
            'filename'            => $fileName,
            'fileExtension'       => $this->getRefDataMap($file->getType()),
            'category'            => $data['details']['category'],
            'documentSubCategory' => $data['details']['documentSubCategory'],
            'isDigital'           => true,
            'isReadOnly'          => true,
            'issuedDate'          => date('Y-m-d H:i:s'),
            'size'                => $file->getSize()
        ];

        $data[$type] = $routeParams[$type];

        $this->makeRestCall(
            'Document',
            'POST',
            $data
        );

        $this->removeTmpData();

        return $this->redirect()->toRoute(
            $type . '/documents',
            $routeParams
        );
    }

    private function getRefDataMap($type)
    {
        if (isset($this->mimeTypeMap[$type])) {
            return $this->mimeTypeMap[$type]['ref_data'];
        }
        return null;
    }

    private function getExtensionMap($type)
    {
        if (isset($this->mimeTypeMap[$type])) {
            return $this->mimeTypeMap[$type]['extension'];
        }
        return null;
    }

    private function formatFilename($input)
    {
        return str_replace([' ', '/'], '_', $input);
    }
}
