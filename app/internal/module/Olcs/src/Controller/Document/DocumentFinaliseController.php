<?php

namespace Olcs\Controller\Document;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Dvsa\Jackrabbit\Data\Object\File;

class DocumentFinaliseController extends AbstractDocumentController
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
        var_dump($data);
        var_dump($this->getRequest()->getFiles());
        die();
        // later...
        /*
        $this->makeRestCall(
            'Document',
            'POST'
        );
        */
    }
}
