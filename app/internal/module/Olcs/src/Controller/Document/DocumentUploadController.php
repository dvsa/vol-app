<?php

namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;

use Dvsa\Jackrabbit\Data\Object\File;

class DocumentUploadController extends DocumentController
{
    public function finaliseAction()
    {
        $contentStore = $this->getContentStore();
        $doc = $contentStore->read(self::TMP_STORAGE_PATH . $this->params()->fromRoute('tmpId'));

        $data = [
            'category' => 'A Category',
            'link' => '<a href=/fooo>Foo</a>'
        ];
        $form = $this->generateFormWithData(
            'finalise-document',
            'processUpload',
            $data
        );
        $view = new ViewModel(
            [
                'form' => $form
            ]
        );
        // @TODO obviously, don't re-use this template; make a generic one if appropriate
        $view->setTemplate('task/add-or-edit');
        return $this->renderView($view, 'Generate letter');
    }


    public function processUpload($data)
    {
        // @TODO fetch the document's meta
        // @TODO delete the tmp file
        // @TODO create the proper file in JR
        // @TODO persist the actual document
        /*
        $this->makeRestCall(
            'Document',
            'POST'
        );
        */
    }
}
