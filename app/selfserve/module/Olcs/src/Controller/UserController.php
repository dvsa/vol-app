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
        } // else is new / add / create

        if ($this->getRequest()->isPost() && $form->isValid()) {

            $data = $form->getData();
            $data = $this->formatSaveData($data);
            $this->getEntityService()->save($data);

            $this->getFlashMessenger()->addSuccessMessage('User updated successfully.');
            return $this->redirect()->toRouteAjax('user', ['action' => 'index'], array(), false);
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
        $output = [];
        $output['main']['id']            = $data['id'];
        $output['main']['version']       = $data['version'];
        $output['main']['memorableWord'] = $data['memorableWord'];
        $output['main']['loginId']       = $data['loginId'];

        $output['main']['emailAddress']  = $data['contactDetails']['emailAddress'];
        $output['main']['emailConfirm']  = $data['contactDetails']['emailAddress'];
        $output['contactDetailsId']      = $data['contactDetails']['id'];
        $output['contactDetailsVersion'] = $data['contactDetails']['version'];
        if (isset($data['contactDetails']['contactType']['id'])) {
            $output['contactType'] = $data['contactDetails']['contactType']['id'];
        } else {
            $output['contactType'] = '';
        }

        $output['main']['familyName']    = $data['contactDetails']['person']['familyName'];
        $output['main']['forename']      = $data['contactDetails']['person']['forename'];
        $output['main']['birthDate']     = $data['contactDetails']['person']['birthDate'];
        $output['personId']              = $data['contactDetails']['person']['id'];
        $output['personVersion']         = $data['contactDetails']['person']['version'];

        // -- roles

        return $output;
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
        $output = [];

        $output['id']      = $data['main']['id'];
        $output['version'] = $data['main']['version'];

        $output['loginId'] = $data['main']['loginId'];

        $output['contactDetails']['emailAddress'] = $data['main']['emailAddress'];
        $output['contactDetails']['id']      = $data['contactDetailsId'];
        $output['contactDetails']['version'] = $data['contactDetailsId'];

        if (empty($data['contactDetailsType'])) {
            $output['contactDetails']['contactType'] = 'ct_team_user';
        } else {
            $output['contactDetails']['contactType'] = $data['contactType'];
        }

        $output['contactDetails']['person']['familyName'] = $data['main']['familyName'];
        $output['contactDetails']['person']['forename']   = $data['main']['forename'];
        $output['contactDetails']['person']['birthDate']  = $data['main']['birthDate'];
        $output['contactDetails']['person']['id']         = $data['personId'];
        $output['contactDetails']['person']['version']    = $data['personVersion'];

        $output['memorableWord'] = $data['main']['memorableWord'];

        return $output;
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

            //die('<pre>' . print_r(array($data), 1));

            $crudAction = null;
            if (isset($data['table'])) {
                $crudAction = $this->getCrudAction(array($data));
            }

            //die('<pre>' . var_export($crudAction, 1));

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
        //die(__METHOD__);

        return $this->save();
    }

    /**
     * Add action - proxy method.
     *
     * @return mixed
     */
    public function editAction()
    {
        //echo(__METHOD__);

        //die('<pre>' . print_r($this->params()->fromRoute(), 1));

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
