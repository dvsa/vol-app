<?php

/**
 * Dashboard Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Traits\CrudTableTrait;
use Olcs\View\Model\User;
use Olcs\View\Model\Form;
use Zend\Http\Request as Request;

/**
 * User Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class UserController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait;
    use CrudTableTrait;

    /**
     * @var string
     */
    protected $serviceName = 'Entity\User';

    /**
     * @var \Common\Service\Entity\UserEntityService
     */
    protected $service = null;

    /**
     * Dashboard index action
     */
    public function indexAction()
    {
        $this->checkForCrudAction();

        /** @var \Common\Service\Entity\UserEntityService $service */
        $service = $this->getEntityService();

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

        $this->getServiceLocator()->get('Script')->loadFiles(['lva-crud']);

        return $view;
    }

    protected function save()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest('User', $this->getRequest());

        $id = $this->params()->fromRoute('id', null);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
        } elseif ($id) {
            $data = $this->getEntityService()->getUserDetails($id);
            $data = $this->formatLoadData($data);
            $form->setData($data);
        }

        if ($this->getRequest()->isPost() && $form->isValid()) {

            $data = $form->getData();
            $data = $this->formatSaveData($data);
            $this->getEntityService()->save($data);

            $this->getFlashMessenger()->addSuccessMessage('User updated successfully.');
            return $this->redirect()->toRouteAjax('user', ['action' => 'index'], [], false);
        }

        $view = new Form();
        $view->setForm($form);

        return $view;
    }

    public function deleteAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = $this->params()->fromRoute('id', null);

            $this->getEntityService()->delete($id);

            $this->getFlashMessenger()->addSuccessMessage('User deleted successfully.');

            return $this->redirect()->toRouteAjax('user', ['action' => 'index'], array(), false);
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('GenericDeleteConfirmation', $request);

        $params = ['sectionText' => $this->getDeleteMessage()];

        return $this->render($this->getDeleteTitle(), $form, $params);
    }

    /**
     * Formats the data from what the service gives us, to what the form needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    public function formatLoadData($data)
    {
        return $this->getServiceLocator()
            ->get('BusinessRuleManager')
            ->get('UserMappingContactDetails')->{__FUNCTION__}($data);
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    public function formatSaveData($data)
    {
        return $this->getServiceLocator()
            ->get('BusinessRuleManager')
            ->get('UserMappingContactDetails')->{__FUNCTION__}($data);
    }

    /**
     * Gets a flash messenger object.
     *
     * @return \Common\Service\Helper\FlashMessengerHelperService
     */
    public function getFlashMessenger()
    {
        return $this->getServiceLocator()->get('Helper\FlashMessenger');
    }

    /**
     * Checks for crud actions.
     *
     * @return \Zend\Http\Response
     */
    public function checkForCrudAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = (array)$request->getPost();

            $crudAction = null;
            if (isset($data['table'])) {
                $crudAction = $this->getCrudAction(array($data));
            }

            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction, ['add'], 'id', null);
            }
        }
    }

    /**
     * Returns a params object. Made literal here.
     *
     * @return \Zend\Mvc\Controller\Plugin\Params
     */
    public function params()
    {
        return $this->getPluginManager()->get('params');
    }

    /**
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        return $this->getEvent()->getRequest();
    }

    /**
     * Add action - proxy method.
     *
     * @return mixed
     */
    public function addAction()
    {
        return $this->save();
    }

    /**
     * Add action - proxy method.
     *
     * @return mixed
     */
    public function editAction()
    {
        return $this->save();
    }

    /**
     * Returns an instance of a service.
     *
     * @return \Common\Service\Entity\UserEntityService
     */
    protected function getEntityService()
    {
        /** @var \Common\Service\Entity\UserEntityService $service */
        return $this->getServiceLocator()->get($this->serviceName);
    }
}
