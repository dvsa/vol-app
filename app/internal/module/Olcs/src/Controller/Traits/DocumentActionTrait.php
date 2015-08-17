<?php

namespace Olcs\Controller\Traits;

use Common\Controller\Traits\GenericUpload;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocuments;
use Dvsa\Olcs\Transfer\Query\Document\Document;
use Zend\View\Model\ViewModel;

/**
 * Document Action Trait
 */
trait DocumentActionTrait
{
    use GenericUpload;

    protected abstract function getDocumentRoute();
    protected abstract function getDocumentRouteParams();
    protected abstract function getDocumentView();

    protected $documentIdentifierName = 'doc';

    public function documentsAction()
    {
        if ($this->getRequest()->isPost()) {
            $action = strtolower($this->params()->fromPost('action'));
            $params = $this->getDocumentRouteParams();

            if ($action === 'split') {

                $id = $this->params()->fromPost('id', []);
                $id = $id[0];

                $response = $this->handleQuery(Document::create(['id' => $id]));
                $data = $response->getResult();

                $docParams = [
                    'file' => $data['identifier'],
                    'name' => $data['filename']
                ];

                $currentUrl = $this->url()->fromRoute(null, [], [], true);
                $documentUrl = $this->url()->fromRoute('getfile', $docParams, ['query' => ['inline' => 1]]);

                $fragment = base64_encode($currentUrl . '|' . $documentUrl);

                return $this->redirect()->toRouteAjax('split-screen', [], ['fragment' => $fragment]);
            }

            if ($action === 'new letter') {
                $action = 'generate';
            }
            if ($action === 'delete' || $action === 'relink') {
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
        $this->handleCommand(DeleteDocuments::create(['ids' => $ids]));

        $this->addSuccessMessage('internal.documents.delete.deleted_successfully');

        return $this->redirect()->toRouteAjax($this->getDocumentRoute(), $this->getDocumentRouteParams());
    }
}
