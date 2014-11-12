<?php
namespace Olcs\Controller\Traits;

use Common\Service\Data\CloseableInterface;
use Zend\View\Model\ViewModel;

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

    public function closeAction()
    {
        $identifierName = $this->getIdentifierName();
        $id = $this->params()->fromRoute($identifierName);

        $response = $this->confirm('Please confirm you wish to close this ' . $this->getIdentifierName() . '?');

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $dataService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\\' . $this->getDataServiceName());

        if ($dataService instanceof CloseableInterface) {
            $dataService->closeEntity($id);
        }
        $this->redirectToIndex();
    }

    /**
     * Reopens an entity and redirects to the index
     */
    public function reopenAction()
    {
        $identifierName = $this->getIdentifierName();
        $id = $this->params()->fromRoute($identifierName);

        $response = $this->confirm('Please confirm you wish to reopen this ' . $this->getIdentifierName() . '?');

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $dataService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\\' . $this->getDataServiceName());

        if ($dataService instanceof CloseableInterface) {
            $dataService->reopenEntity($id);
        }

        $this->redirectToIndex();
    }

    /**
     * returns the action array to generate the close/reopen button for a given entity
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
