<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Traits\CrudTableTrait;
use Dvsa\Olcs\Transfer\Command\User\CreateUserSelfserve as CreateDto;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserSelfserve as UpdateDto;
use Dvsa\Olcs\Transfer\Command\User\DeleteUserSelfserve as DeleteDto;
use Dvsa\Olcs\Transfer\Query\User\UserListSelfserve as ListDto;
use Dvsa\Olcs\Transfer\Query\User\UserSelfserve as ItemDto;
use Olcs\View\Model\User;
use Olcs\View\Model\Form;
use Zend\View\Model\ViewModel;

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
     * Dashboard index action
     *
     * @return \Olcs\View\Model\User
     */
    public function indexAction()
    {
        $crudAction = $this->checkForCrudAction();

        if (isset($crudAction)) {
            return $crudAction;
        }

        $params = [
            'page'    => $this->getPluginManager()->get('params')->fromQuery('page', 1),
            'sort'    => $this->getPluginManager()->get('params')->fromQuery('sort', 'id'),
            'order'   => $this->getPluginManager()->get('params')->fromQuery('order', 'DESC'),
            'limit'   => $this->getPluginManager()->get('params')->fromQuery('limit', 10),
        ];

        $params['query'] = $this->getPluginManager()->get('params')->fromQuery();

        $response = $this->handleQuery(
            ListDto::create(
                $params
            )
        );

        if ($response->isOk()) {
            $users = $response->getResult();
        } else {
            $this->getFlashMessenger()->addErrorMessage('unknown-error');
            $users = [];
        }

        $view = new User();
        $view->setServiceLocator($this->getServiceLocator());
        $view->setUsers($users, $params);

        $this->getServiceLocator()->get('Script')->loadFiles(['lva-crud']);

        return $view;
    }

    /**
     * Save
     *
     * @return \Olcs\View\Model\Form|\Zend\Http\Response
     */
    protected function save()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest('User', $this->getRequest());

        $id = $this->params()->fromRoute('id', null);
        $data = [];

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }

            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $this->formatSaveData($form->getData());

                if ((!empty($data['id']))) {
                    $command = UpdateDto::create($data);
                    $successMessage = 'manage-users.update.success';
                } else {
                    $command = CreateDto::create($data);
                    $successMessage = 'manage-users.create.success';
                }
                $response = $this->handleCommand($command);

                if ($response->isOk()) {
                    $this->getFlashMessenger()->addSuccessMessage($successMessage);
                    return $this->redirectToIndex();
                } else {
                    $result = $response->getResult();

                    if (!empty($result['messages'])) {
                        $form->setMessages(
                            [
                                'main' => $result['messages']
                            ]
                        );
                    } else {
                        $this->getFlashMessenger()->addErrorMessage('unknown-error');
                    }
                }
            }
        } elseif ($id) {
            $response = $this->handleQuery(
                ItemDto::create(
                    ['id' => $id]
                )
            );
            if (!$response->isOk()) {
                $this->getFlashMessenger()->addErrorMessage('unknown-error');
                return $this->redirectToIndex();
            }

            $data = $this->formatLoadData($response->getResult());
            $form->setData($data);
        }

        $view = new ViewModel(
            [
                'form' => $this->alterForm($form, $data),
            ]
        );
        $view->setTemplate('user-form');

        return $view;
    }

    /**
     * Alter form
     *
     * @param \Olcs\View\Model\Form $form Form
     * @param array                 $data Data
     *
     * @return \Olcs\View\Model\Form
     */
    public function alterForm($form, $data)
    {
        if (!isset($data['main']['currentPermission']) || ($data['main']['currentPermission'] !== 'tm')) {
            // the option should only be available if editing already TM user
            $form->get('main')
                ->get('permission')
                ->unsetValueOption('tm');
        }

        return $form;
    }

    /**
     * Delete action
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function deleteAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }

            $response = $this->handleCommand(
                DeleteDto::create(
                    ['id' => $this->params()->fromRoute('id', null)]
                )
            );

            if ($response->isOk()) {
                $this->getFlashMessenger()->addSuccessMessage('manage-users.delete.success');
            } elseif ($response->isClientError()) {
                $this->getFlashMessenger()->addErrorMessage('manage-users.delete.error');
            } else {
                $this->getFlashMessenger()->addErrorMessage('unknown-error');
            }

            return $this->redirectToIndex();
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
     * @param array $data Data
     *
     * @return array
     */
    public function formatLoadData($data)
    {
        $output = [];
        $output['main']['id']            = $data['id'];
        $output['main']['version']       = $data['version'];
        $output['main']['loginId']       = $data['loginId'];
        $output['main']['permission']    = $data['permission'];
        $output['main']['currentPermission'] = $data['permission'];
        $output['main']['translateToWelsh']  = $data['translateToWelsh'];

        $output['main']['emailAddress']  = $data['contactDetails']['emailAddress'];
        $output['main']['emailConfirm']  = $data['contactDetails']['emailAddress'];

        $output['main']['familyName']    = $data['contactDetails']['person']['familyName'];
        $output['main']['forename']      = $data['contactDetails']['person']['forename'];

        return $output;
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     * This is mapping, not business logic.
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatSaveData($data)
    {
        $output = [];

        $output['id']      = $data['main']['id'];
        $output['version'] = $data['main']['version'];

        $output['loginId'] = $data['main']['loginId'];
        $output['permission'] = $data['main']['permission'];
        $output['translateToWelsh'] = $data['main']['translateToWelsh'];

        $output['contactDetails']['emailAddress'] = $data['main']['emailAddress'];

        $output['contactDetails']['person']['familyName'] = $data['main']['familyName'];
        $output['contactDetails']['person']['forename']   = $data['main']['forename'];

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
     * @return \Zend\Http\Response|null
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

        return null;
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
     * Get request
     *
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        return $this->getEvent()->getRequest();
    }

    /**
     * Add action - proxy method.
     *
     * @return \Olcs\View\Model\Form|\Zend\Http\Response
     */
    public function addAction()
    {
        return $this->save();
    }

    /**
     * Add action - proxy method.
     *
     * @return \Olcs\View\Model\Form|\Zend\Http\Response
     */
    public function editAction()
    {
        return $this->save();
    }

    /**
     * Redirects to index
     *
     * @return \Zend\Http\Response
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax('manage-user', ['action' => 'index'], array(), false);
    }
}
