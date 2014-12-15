<?php

namespace Olcs\Controller\Traits;

/**
 * Class DocumentActionTrait
 * @package Olcs\Controller
 */
trait DocumentActionTrait
{
    protected abstract function getDocumentRoute();
    protected abstract function getDocumentRouteParams();
    protected abstract function getDocumentView();

    public function documentsAction()
    {
        if ($this->getRequest()->isPost()) {
            $action = strtolower($this->params()->fromPost('action'));

            if ($action === 'new letter') {
                $action = 'generate';
            }

            $params = $this->getDocumentRouteParams();
            $route  = $this->getDocumentRoute().'/'.$action;
            return $this->redirect()->toRoute($route, $params);
        }

        $view = $this->getDocumentView();

        $this->loadScripts(['documents', 'table-actions']);

        $view->setTemplate('layout/docs-attachments-list');
        $view->setTerminal(
            $this->getRequest()->isXmlHttpRequest()
        );

        return $this->renderView($view);
    }
}
