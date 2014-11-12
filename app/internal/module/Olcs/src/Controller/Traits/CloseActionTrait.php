<?php

namespace Olcs\Controller\Traits;
use Common\Service\Data\CloseableInterface;

/**
 * Class CloseActionTrait
 * @package Olcs\Controller
 */
trait CloseActionTrait
{
    abstract public function getIdentifierName();
    abstract public function getServiceLocator();
    abstract public function addErrorMessage($message);
    abstract public function redirectToIndex();
    abstract public function getDataServiceName();

    /**
     * Performs a close action and redirects to the index
     */
    public function closeAction()
    {
        $form = $this->generateFormWithData('Confirm', 'closeEntity', $this->getDataForForm());

        $view = $this->getView();

        $view->setVariable('form', $form);
        $view->setVariable('label', 'Please confirm you wish to close this submission?');

        $view->setTemplate('crud/confirm');

        return $this->renderView($view);
    }

    /**
     * Reopens an entity and redirects to the index
     */
    public function reopenAction()
    {
        $form = $this->generateFormWithData('Confirm', 'reopenEntity', $this->getDataForForm());

        $view = $this->getView();

        $view->setVariable('form', $form);
        $view->setVariable('label', 'Please confirm you wish to reopen this submission?');

        $view->setTemplate('crud/confirm');

        return $this->renderView($view);
    }

    protected function reopenEntity()
    {
        $data = $this->getEntityData();
        $this->updateClosedDate($data);
        $this->addErrorMessage('Reopened successful');
        $this->redirectToIndex();
    }

    protected function closeEntity()
    {
        $data = $this->getEntityData();
        $now = date('Y-m-d h:i:s');

        $this->updateClosedDate($data, $now);

        $this->addErrorMessage('Closed successfully');

        $this->redirectToIndex();
    }

    protected function getEntityData()
    {
        $identifierName = $this->getIdentifierName();
        $id = $this->params()->fromRoute($identifierName);

        $dataService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\\' . $this->getDataServiceName());

        if ($dataService instanceof CloseableInterface) {
            return $dataService->fetchData($id);
        }
        return array();
    }

    protected function updateClosedDate($data, $date = null)
    {
        return $this->makeRestCall(
            $this->getDataServiceName(),
            'PUT',
            [
                'id' => $data['id'],
                'version' => $data['version'],
                'closedDate' => $date
            ]
        );
    }

    /**
     * returns the action array to generate the close/reopen button for a given submission/case
     *
     * @param $entity
     * @param $case
     * @return array|null
     */
    public function generateCloseActionButtonArray()
    {
        $identifierName = $this->getIdentifierName();
        $id = $this->params()->fromRoute($identifierName);

        $dataService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\\' . $this->getDataServiceName());

        if ($dataService instanceof CloseableInterface) {
            if ($dataService->canReopen($id)) {
                return $dataService->getReopenButton($id);
            }
            if ($dataService->canClose($id)) {
                return $dataService->getCloseButton($id);
            }
        }
        return null;
    }
}
