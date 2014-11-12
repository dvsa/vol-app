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
        $identifierName = $this->getIdentifierName();
        $id = $this->params()->fromRoute($identifierName);
        //$this->makeRestCall($this->getDeleteServiceName(), 'DELETE', ['id' => $id]);

        $this->addErrorMessage('Closed sucessfully');

        $this->redirectToIndex();
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
        $entityId = $this->params()->fromRoute($identifierName);

        $dataService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\\' . $this->getDataServiceName());

        if ($dataService instanceof CloseableInterface) {
            if ($dataService->canReopen($entityId)) {
                return $dataService->getReopenButton($entityId);
            }
            if ($dataService->canClose($entityId)) {
                return $dataService->getCloseButton($entityId);
            }
        }
        return null;
    }
}
