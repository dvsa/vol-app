<?php

namespace Common\Controller\Traits;

use Laminas\Http\Response;

/**
 * CheckForCrudAction trait
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
trait CheckForCrudAction
{
    /**
     * Check for crud actions
     *
     * @param string $route       Route
     * @param array  $params      Parameters from query
     * @param string $itemIdParam Item
     *
     * @return boolean|Response
     */
    protected function checkForCrudAction($route = null, $params = [], $itemIdParam = 'id')
    {
        $action = $this->getCrudActionFromPost();

        if (empty($action)) {
            return false;
        }

        if (!is_array($action)) {
            $action = strtolower($action);
        }

        $response = $this->checkForAlternativeCrudAction($action);

        if ($response instanceof \Laminas\Http\Response) {
            return $response;
        }

        $params = array_merge($params, ['action' => $action]);

        $options = [];

        $action = $this->getActionFromFullActionName($action);

        if (!in_array($action, $this->getNoActionIdentifierRequired())) {
            $post = (array)$this->getRequest()->getPost();

            $id = $post['table']['id'] ?? $this->params()->fromPost('id');

            if (empty($id)) {
                $this->crudActionMissingId();
                return false;
            }

            if (is_array($id) && count($id) === 1) {
                $id = $id[0];
            }

            // If we have an array of id's we need to use a query string param rather than the route
            if (is_array($id)) {
                $options = [
                    'query' => [
                        $itemIdParam => $id
                    ]
                ];
            } else {
                $params[$itemIdParam] = $id;
            }
        }

        return $this->redirect()->toRoute($route, $params, $options, true);
    }

    /**
     * We can now extend our check for crud action
     *
     * @return string
     */
    protected function getCrudActionFromPost()
    {
        $post = (array)$this->getRequest()->getPost();

        return $post['table']['action'] ?? $post['action'] ?? null;
    }

    /**
     * Do nothing, this method can be overridden to hijack the crud action check
     *
     * @param string $action Action
     *
     * @return void
     */
    protected function checkForAlternativeCrudAction($action)
    {
    }

    /**
     * Get the last part of the action from the action name
     *
     * @param string $action action
     *
     * @return string
     */
    protected function getActionFromFullActionName($action = null)
    {
        if ($action == null) {
            return '';
        }

        if (strstr($action, '-') === '' || strstr($action, '-') === '0' || !str_contains($action, '-')) {
            return $action;
        }

        $parts = explode('-', $action);
        return array_pop($parts);
    }

    /**
     * Get no action identifier required
     *
     * @return array
     */
    protected function getNoActionIdentifierRequired()
    {
        return ['add'];
    }

    /**
     * Called when a crud action is missing a required ID
     *
     * @return Response
     */
    protected function crudActionMissingId()
    {
        $this->flashMessengerHelper->addWarningMessage('please-select-row');
        return $this->redirect()->toRoute(null, [], [], true);
    }
}
