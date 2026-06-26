<?php

namespace Common\Controller\Lva\Traits;

/**
 * Crud action trait
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait CrudActionTrait
{
    /**
     * Check if we have a crud action in the form table data, if so return the table data, if not return null
     *
     * @param array $formTables Tables
     *
     * @return array
     */
    protected function getCrudAction(array $formTables = [])
    {
        foreach ($formTables as $table) {
            if (isset($table['action'])) {
                return $table;
            }
        }

        return null;
    }

    /**
     * Return selected CRUD action
     *
     * @param array $data Post Data
     *
     * @return string
     */
    protected function getActionFromCrudAction($data)
    {
        if (is_array($data['action'])) {
            return strtolower(array_keys($data['action'])[0]);
        }

        return strtolower($data['action']);
    }

    /**
     * Redirect to the most appropriate CRUD action
     *
     * @param array  $data             Data
     * @param array  $rowsNotRequired  Action
     * @param string $childIdParamName Child route identifier
     * @param string $route            Route
     *
     * @return \Laminas\Http\Response
     */
    protected function handleCrudAction(
        $data,
        $rowsNotRequired = ['add'],
        $childIdParamName = 'child_id',
        $route = null
    ) {
        $action = $this->getActionFromCrudAction($data);

        if (is_array($data['action'])) {
            $data['id'] = array_keys($data['action'][$action])[0];
        }

        $routeParams = ['action' => $data['routeAction'] ?? $action];

        if (!in_array($action, $rowsNotRequired, true)) {
            if (!isset($data['id'])) {
                $this->flashMessengerHelper->addWarningMessage('please-select-row');
                return $this->redirect()->refresh();
            }

            if (is_array($data['id'])) {
                $data['id'] = implode(',', $data['id']);
            }

            $routeParams[$childIdParamName] = $data['id'];
        }

        $options = ['query' => $this->getRequest()->getQuery()->toArray()];

        if ($route === null) {
            $route = ($this->getBaseRoute() ? $this->getBaseRoute() . '/action' : null);
        }

        return $this->redirect()->toRoute($route, $routeParams, $options, true);
    }

    /**
     * Return base route
     *
     * @return null|string
     */
    protected function getBaseRoute()
    {
        if (empty($this->baseRoute)) {
            return null;
        }

        if (isset($this->lva)) {
            return sprintf($this->baseRoute, $this->lva);
        }

        return $this->baseRoute;
    }
}
