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
    abstract public function addErrorMessage($message);
    abstract public function redirectToIndex();
    abstract public function getDataService();
    abstract public function renderView($view, $pageTitle = NULL, $pageSubTitle = NULL);

    /**
     * Gets the id of the entity to close from the route
     * @return integer
     */
    public function getIdToClose()
    {
        $identifierName = $this->getIdentifierName();
        $id = $this->params()->fromRoute($identifierName);
        return $id;
    }

    /**
     * Close the entity (calls data service closeEntity())
     * @return mixed
     */
    public function closeAction()
    {
        $id = $this->getIdToClose();

        $response = $this->confirm('Are you sure you wish to close this ' . $this->getIdentifierName() . '?');

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $dataService = $this->getDataService();

        if ($dataService instanceof CloseableInterface) {
            $dataService->closeEntity($id);
        }

        return $this->redirectToIndex();
    }

    /**
     * Reopens an entity and redirects to the index
     */
    public function reopenAction()
    {
        $id = $this->getIdToClose();

        $response = $this->confirm('Are you sure you wish to reopen this ' . $this->getIdentifierName() . '?');

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $dataService = $this->getDataService();

        if ($dataService instanceof CloseableInterface) {
            $dataService->reopenEntity($id);
        }

        return $this->redirectToIndex();
    }

    /**
     * Returns the action array to generate the close/reopen button for a given entity
     *
     * @return array|null
     */
    public function generateCloseActionButtonArray()
    {
        $id = $this->getIdToClose();
        $dataService = $this->getDataService();

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
