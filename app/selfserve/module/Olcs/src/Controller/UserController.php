<?php

/**
 * Dashboard Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Olcs\View\Model\User;

/**
 * User Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class UserController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait;

    /**
     * Dashboard index action
     */
    public function indexAction()
    {
        /** @var \Common\Service\Entity\UserEntityService $service */
        $service = $this->getServiceLocator()->get('Entity\User');

        $params = [
            'page'    => $this->getPluginManager()->get('params')->fromQuery('page', 1),
            'sort'    => $this->getPluginManager()->get('params')->fromQuery('sort', 'id'),
            'order'   => $this->getPluginManager()->get('params')->fromQuery('order', 'DESC'),
            'limit'   => $this->getPluginManager()->get('params')->fromQuery('limit', 10),
        ];

        $params['query'] = $this->getPluginManager()->get('params')->fromQuery();

        $users = $service->getList($params);

        $view = new User();
        $view->setServiceLocator($this->getServiceLocator());
        $view->setUsers($users, $params);

        return $view;
    }

    /**
     * Proxies to the get query or get param.
     *
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
    public function getQueryOrRouteParam($name, $default = null)
    {
        if ($queryValue = $this->params()->fromQuery($name, $default)) {
            return $queryValue;
        }

        if ($queryValue = $this->params()->fromRoute($name, $default)) {
            return $queryValue;
        }

        return $default;
    }
}
