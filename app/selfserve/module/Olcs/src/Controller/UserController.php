<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Traits\CrudTableTrait;
use Common\FeatureToggle;
use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\User\CreateUserSelfserve as CreateDto;
use Dvsa\Olcs\Transfer\Command\User\DeleteUserSelfserve as DeleteDto;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserSelfserve as UpdateDto;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Dvsa\Olcs\Transfer\Query\User\UserListSelfserve as ListDto;
use Dvsa\Olcs\Transfer\Query\User\UserSelfserve as ItemDto;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use Olcs\View\Model\User;
use LmcRbacMvc\Service\AuthorizationService;

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
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param User $user
     * @param ScriptFactory $scriptFactory
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TranslationHelperService $translationHelper
     * @param GuidanceHelperService $guidanceHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected User $user,
        protected ScriptFactory $scriptFactory,
        protected FormHelperService $formHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected TranslationHelperService $translationHelper,
        protected GuidanceHelperService $guidanceHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Dashboard index action
     *
     * @return User|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        $crudAction = $this->checkForCrudAction();

        if (isset($crudAction)) {
            return $crudAction;
        }

        $params = [
            'page' => $this->params()->fromQuery('page', 1),
            'sort' => $this->params()->fromQuery('sort', 'id'),
            'order' => $this->params()->fromQuery('order', 'DESC'),
            'limit' => $this->params()->fromQuery('limit', 10),
            'query' => $this->params()->fromQuery(),
        ];

        $response = $this->handleQuery(
            ListDto::create(
                $params
            )
        );

        if ($response->isOk()) {
            $users = $response->getResult();
        } else {
            $this->getFlashMessenger()->addUnknownError();
            $users = [];
        }

        $view = $this->user;
        assert($view instanceof User);

        $view->setUsers(
            $users,
            $params
        );

        $this->scriptFactory->loadFiles(['lva-crud']);

        return $view;
    }

    /**
     * Save
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    protected function save()
    {
        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->getFormHelper()->createFormWithRequest('User', $request);

        $id = $this->params()->fromRoute('id', null);
        $data = [];

        if ($id) {
            $response = $this->handleQuery(
                ItemDto::create(
                    ['id' => $id]
                )
            );
            if (!$response->isOk()) {
                $this->getFlashMessenger()->addUnknownError();
                return $this->redirectToIndex();
            }

            $data = $this->formatLoadData($response->getResult());
            $form->setData($data);
            $this->lockNameFields($form);
        }

        if ($request->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }

            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                if (!empty($data['main']['id'])) {
                    $data = $this->formatSaveData($form->getData());
                    $command = UpdateDto::create($data);
                    $successMessage = 'manage-users.update.success';
                } else {
                    $data = $this->formatSaveDataForCreate($form->getData());
                    $command = CreateDto::create($data);
                    $successMessage = 'manage-users.create.success';
                }
                $response = $this->handleCommand($command);

                if ($response->isOk()) {
                    $this->getFlashMessenger()->addSuccessMessage($successMessage);
                    return $this->redirectToIndex();
                }

                $result = $response->getResult();

                if (!empty($result['messages'])) {
                    $form->setMessages(
                        [
                            'main' => $result['messages'],
                        ]
                    );
                } else {
                    $this->getFlashMessenger()->addUnknownError();
                }
            }
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
     * @param \Laminas\Form\FormInterface $form Form
     * @param array                    $data Data
     *
     * @return \Laminas\Form\FormInterface
     */
    public function alterForm($form, $data)
    {
        // Hide TC option when feature toggle is disabled
        if (!$this->handleQuery(IsEnabledQry::create(['ids' => [FeatureToggle::TRANSPORT_CONSULTANT_ROLE]]))->getResult()['isEnabled']) {
            $form->get('main')
                ->get('permission')
                ->unsetValueOption('tc');
        }

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
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function deleteAction()
    {
        $userId = (int)$this->params()->fromRoute('id', null);

        //  check - user can not delete himself
        if ($userId === $this->getCurrentUser()['id']) {
            return $this->redirectToIndex();
        }

        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formHelper
            ->createFormWithRequest('GenericDeleteConfirmation', $request);

        if ($request->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }

            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $response = $this->handleCommand(DeleteDto::create(['id' => $userId]));

                if ($response->isOk()) {
                    $this->getFlashMessenger()->addSuccessMessage('manage-users.delete.success');
                } elseif ($response->isClientError()) {
                    $this->getFlashMessenger()->addErrorMessage('manage-users.delete.error');
                } else {
                    $this->getFlashMessenger()->addUnknownError();
                }

                return $this->redirectToIndex();
            }
        }

        $params = ['sectionText' => $this->getDeleteMessage()];

        return $this->render($this->getDeleteTitle(), $form, $params);
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-user';
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
    public function formatSaveData(array $data)
    {
        $output = [];

        $output['id'] = $data['main']['id'] ?? '';
        $output['version'] = $data['main']['version'];
        $output['loginId'] = $data['main']['loginId'];
        $output['permission'] = $data['main']['permission'];
        $output['translateToWelsh'] = $data['main']['translateToWelsh'];
        $output['contactDetails']['emailAddress'] = $data['main']['emailAddress'];

        return $output;
    }

    /**
     * Formats data for create user command
     *
     * @return array
     */
    public function formatSaveDataForCreate(array $data)
    {
        $output = $this->formatSaveData($data);

        $output['contactDetails']['person']['familyName'] = $data['main']['familyName'];
        $output['contactDetails']['person']['forename'] = $data['main']['forename'];

        return $output;
    }

    /**
     * Gets a flash messenger object.
     *
     * @return \Common\Service\Helper\FlashMessengerHelperService
     */
    public function getFlashMessenger()
    {
        return $this->flashMessengerHelper;
    }

    /**
     * Checks for crud actions.
     *
     * @return \Laminas\Http\Response|null
     */
    public function checkForCrudAction()
    {
        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $crudAction = null;
            if (isset($data['table'])) {
                $crudAction = $this->getCrudAction([$data]);
            }

            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction, ['add'], 'id', null);
            }
        }

        return null;
    }

    /**
     * Add action - proxy method.
     *
     * @return \Olcs\View\Model\Form|\Laminas\Http\Response
     */
    public function addAction()
    {
        return $this->save();
    }

    /**
     * Add action - proxy method.
     *
     * @return \Olcs\View\Model\Form|\Laminas\Http\Response
     */
    public function editAction()
    {
        return $this->save();
    }

    /**
     * Redirects to index
     *
     * @return \Laminas\Http\Response
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax('manage-user', ['action' => 'index'], [], false);
    }

    protected function lockNameFields(Form $form): void
    {
        $fieldSet = $form->get('main');

        $this->getFormHelper()->lockElement($fieldSet->get('forename'), 'name-change.locked.tooltip.message');
        $this->getFormHelper()->disableElement($form, 'main->forename');

        $this->getFormHelper()->lockElement($fieldSet->get('familyName'), 'name-change.locked.tooltip.message');
        $this->getFormHelper()->disableElement($form, 'main->familyName');

        $this->getFormHelper()->disableElement($form, 'main->loginId');

        $message = $this->translationHelper->translate('name-change.locked.guidance.message');
        $this->guidanceHelper->append($message);
    }

    /**
     * @return FormHelperService
     */
    protected function getFormHelper()
    {
        return $this->formHelper;
    }
}
