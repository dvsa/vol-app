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

        $dataService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\\' . $this->getDataServiceName());

        if ($dataService instanceof CloseableInterface) {
            $data = $dataService->fetchData($id);
        }

        $this->makeRestCall(
            $this->getDataServiceName(),
            'PUT',
            [
                'id' => $id,
                'version' => $data['version'],
                'closedDate' => date('Y-m-d h:i:s')
            ]
        );

        $this->addErrorMessage('Closed successfully');

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
