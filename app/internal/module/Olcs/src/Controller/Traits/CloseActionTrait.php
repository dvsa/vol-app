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
    abstract public function renderView($view, $pageTitle = null, $pageSubTitle = null);

    /**
     * Fetches the id of the entity to close from the route based on identifierName
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
     *
     * @param integer $id|null
     * @return mixed
     */
    public function closeAction($id = null)
    {
        $id = empty($id) ? $this->getIdToClose($id) : $id;

        $response = $this->confirm(
            'Are you sure you wish to close this ' . $this->getEntityDisplayName() . '?'
        );

        if ($response instanceof ViewModel) {
            $this->pageLayoutInner = null;
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
     *
     * @param integer $id|null
     * @return mixed
     */
    public function reopenAction($id = null)
    {
        $id = empty($id) ? $this->getIdToClose($id) : $id;

        $response = $this->confirm(
            'Are you sure you wish to reopen this ' . $this->getEntityDisplayName() . '?'
        );

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
     * @param integer $id|null
     * @return array|null
     */
    public function generateCloseActionButtonArray($id = null)
    {
        $id = empty($id) ? $this->getIdToClose($id) : $id;

        $dataService = $this->getDataService();

        if ($dataService instanceof CloseableInterface) {
            if ($dataService->canReopen($id)) {
                return $this->generateButton('reopen');
            }
            if ($dataService->canClose($id)) {

                return $this->generateButton('close');
            }
        }
        return null;
    }

    /**
     * Generate the button array
     * @param string $action
     * @return array
     */
    public function generateButton($action)
    {
        $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $routeParams = $routeMatch->getParams();
        $routeParams['action'] = $action;

        return [
            'label' => ucfirst($action) . ' ' . $this->getEntityDisplayName(),
            'route' => $routeMatch->getMatchedRouteName(),
            'params' => $routeParams
        ];
    }
}
