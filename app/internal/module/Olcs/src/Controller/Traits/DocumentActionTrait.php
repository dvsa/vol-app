<?php

namespace Olcs\Controller\Traits;

use Common\Controller\Traits\GenericUpload;
use Zend\View\Model\ViewModel;

/**
 * Class DocumentActionTrait
 * @package Olcs\Controller
 */
trait DocumentActionTrait
{
    protected abstract function getDocumentRoute();
    protected abstract function getDocumentRouteParams();
    protected abstract function getDocumentView();

    use GenericUpload;

    protected $documentIdentifierName = 'tmpId';

    public function documentsAction()
    {
        if ($this->getRequest()->isPost()) {
            $action = strtolower($this->params()->fromPost('action'));
            $params = $this->getDocumentRouteParams();

            if ($action === 'new letter') {
                $action = 'generate';
            }
            if ($action === 'delete') {
                $ids = $this->params()->fromPost('id', []);
                $params = array_merge($params, [$this->documentIdentifierName => implode(',', $ids)]);
            }
            $route  = $this->getDocumentRoute() . '/' . $action;
            return $this->redirect()->toRoute($route, $params);
        }

        $view = $this->getDocumentView();

        $this->loadScripts(['documents', 'table-actions']);

        $view->setTemplate('layout/docs-attachments-list');

        return $this->renderView($view);
    }

    /**
     * Performs a delete document action and redirects to the index
     */
    public function deleteDocumentAction()
    {
        $id = $this->params()->fromRoute($this->documentIdentifierName);

        $translator = $this->getServiceLocator()->get('translator');
        $response = $this->confirm($translator->translate('internal.documents.delete.delete_message'));

        if ($response instanceof ViewModel) {
            $response->setTerminal(true);
            return $response;
        }

        $ids = explode(',', $id);
        foreach ($ids as $singleId) {
            $this->deleteFile($singleId);
        }

        $this->addErrorMessage('internal.documents.delete.deleted_successfully');

        return $this->redirect()->toRouteAjax($this->getDocumentRoute(), $this->getDocumentRouteParams());
    }
}
