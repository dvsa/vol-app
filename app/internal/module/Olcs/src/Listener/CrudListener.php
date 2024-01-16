<?php

/**
 * Listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Listener;

use Common\Service\Helper\FlashMessengerHelperService;
use Laminas\Mvc\MvcEvent;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\EventManager\ListenerAggregateInterface;

/**
 * Listener
 *
 * @Note this has been reused for the new abstract internal controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CrudListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    protected $controller;

    protected $identifier;

    protected $defaultCrudConfig = [
        'add' => [
            'requireRows' => false
        ],
        'edit' => [
            'requireRows' => true
        ],
        'delete' => [
            'requireRows' => true
         ]
    ];

    protected $crudConfig = [];
    protected FlashMessengerHelperService $flashMessenger;

    /**
     * Pass the controller in
     *
     * @param \Laminas\Mvc\Controller\AbstractActionController $controller
     */
    public function __construct($controller, $identifier = 'id', array $crudConfig = [], FlashMessengerHelperService $flashMessenger)
    {
        $this->controller = $controller;
        $this->identifier = $identifier;
        $this->crudConfig = array_merge($this->defaultCrudConfig, $crudConfig);
        $this->flashMessenger = $flashMessenger;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 20);
    }

    /**
     * Check for crud actions before hitting the controller action
     *
     * @param MvcEvent $e
     * @return mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        // If we are not posting we can return early
        $request = $e->getRequest();
        if (!$request->isPost()) {
            return;
        }

        $postData = (array)$request->getPost();

        if ($this->hasCancelled($postData)) {
            $this->flashMessenger->addInfoMessage('flash-discarded-changes');
            return $this->setResult($e, $this->controller->redirectTo([]));
        }

        // If we don't have a table and action
        if (!$this->hasCrudAction($postData)) {
            return;
        }

        $routeName = $e->getRouteMatch()->getMatchedRouteName();

        // Grab the crud config from the controller
        $crudConfig = $this->getCrudConfig($routeName);

        $requestedAction = $this->formatAction($postData);

        // @NOTE If we are not expecting the action then bail
        if (!isset($crudConfig[$requestedAction])) {
            return;
        }

        $actionConfig = $crudConfig[$requestedAction];

        $ids = $this->formatIds($postData);

        if ($actionConfig['requireRows'] && $ids === null) {
            $this->flashMessenger->addWarningMessage('please-select-row');
            return $this->setResult($e, $this->controller->redirect()->refresh());
        }

        $params = ['action' => $requestedAction];

        if (isset($actionConfig['requireRows']) && $actionConfig['requireRows']) {
            $params[$this->identifier] = $ids;
        }

        // permits controller to send actions to different routes with different controllers (see OLCS-10851)
        if (isset($actionConfig['route'])) {
            return $this->setResult(
                $e,
                $this->controller->redirect()->toRoute(
                    $actionConfig['route'],
                    $params,
                    [],
                    true
                )
            );
        }

        return $this->setResult($e, $this->controller->redirect()->toRoute(null, $params, [], true));
    }

    /**
     * Check if the user has cancelled the action
     *
     * @param array $postData
     * @return boolean
     */
    protected function hasCancelled($postData)
    {
        return isset($postData['form-actions']['cancel']);
    }

    /**
     * Get the crud config or use the default
     *
     * @param string $routeName
     * @return array
     */
    protected function getCrudConfig($routeName)
    {
        return $this->crudConfig;
    }

    /**
     * Set the event result
     *
     * @param MvcEvent $e
     * @param mixed $result
     * @return mixed
     */
    protected function setResult($e, $result)
    {
        $e->setResult($result);

        return $result;
    }

    /**
     * Check if the post has a crud action
     *
     * @param array $postData
     * @return boolean
     */
    protected function hasCrudAction($postData)
    {
        return isset($postData['table']) && isset($postData['action']);
    }

    /**
     * Format the action from the crud action
     *
     * @param array $action
     * @return string
     */
    protected function formatAction($action)
    {
        if (is_array($action['action'])) {
            $action['action'] = key($action['action']);
        }

        return strtolower($action['action']);
    }

    /**
     * Format the id's from the crud action
     *
     * @param array $postData
     * @return string
     */
    protected function formatIds($postData)
    {
        if (is_array($postData['action'])) {
            $id = key(reset($postData['action']));
        } elseif (isset($postData['id'])) {
            $id = $postData['id'];
        } else {
            return null;
        }

        if (is_array($id)) {
            return implode(',', $id);
        }

        return $id;
    }
}
