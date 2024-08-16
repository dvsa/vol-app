<?php

namespace Olcs\Controller\Traits;

use Common\Controller\Traits\GenericUpload;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocuments;
use Dvsa\Olcs\Transfer\Query\Document\Document;
use Laminas\View\Model\ViewModel;

/**
 * Document Action Trait
 */
trait DocumentActionTrait
{
    use GenericUpload;

    /**
     * Route (prefix) for document action redirects
     *
     * @return string
     */
    abstract protected function getDocumentRoute();

    /**
     * Route params for document action redirects
     *
     * @return array
     */
    abstract protected function getDocumentRouteParams();

    /**
     * Get view model for document action
     *
     * @return \Laminas\View\Model\ViewModel
     */
    abstract protected function getDocumentView();

    /**
     * Get configured document form
     *
     * @return \Laminas\Form\Form
     */
    abstract protected function getConfiguredDocumentForm();

    protected $documentIdentifierName = 'doc';

    /**
     * Get left view
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(['form' => $this->getConfiguredDocumentForm()]);
        $view->setTemplate('sections/docs/partials/left');

        return $view;
    }

    /**
     * Documents action
     *
     * @return \Laminas\Http\Response
     */
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
                    'identifier' => $data['id']
                ];

                $currentUrl = $this->url()->fromRoute(
                    null,
                    [],
                    ['query' => $this->getRequest()->getQuery()->toArray()],
                    true
                );
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
            return $this->redirect()->toRoute(
                $route,
                $params,
                ['query' => $this->getRequest()->getQuery()->toArray()]
            );
        }

        $view = $this->getDocumentView();

        $this->loadScripts(['documents', 'table-actions']);

        $view->setTemplate('pages/table');

        return $this->renderView($view);
    }

    /**
     * Performs a delete document action and redirects to the index
     *
     * @return \Laminas\Http\Response
     */
    public function deleteDocumentAction()
    {
        $id = $this->params()->fromRoute($this->documentIdentifierName);

        $translator = $this->translationHelper;
        $response = $this->confirm($translator->translate('internal.documents.delete.delete_message'));

        if ($response instanceof ViewModel) {
            $this->placeholder()->setPlaceholder('pageTitle', 'Delete document');
            return $this->viewBuilder()->buildView($response);
        }

        $ids = explode(',', $id);
        $deleteResponse = $this->handleCommand(DeleteDocuments::create(['ids' => $ids]));

        if ($deleteResponse->isOk()) {
            $this->addSuccessMessage('internal.documents.delete.deleted_successfully');
        } else {
            $this->addErrorMessage('unknown-error');
        }

        return $this->redirect()->toRouteAjax(
            $this->getDocumentRoute(),
            $this->getDocumentRouteParams(),
            ['query' => $this->getRequest()->getQuery()->toArray()]
        );
    }
}
