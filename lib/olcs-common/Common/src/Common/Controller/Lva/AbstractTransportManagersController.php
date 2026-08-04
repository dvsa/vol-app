<?php

namespace Common\Controller\Lva;

use Common\Controller\Lva\Adapters\AbstractTransportManagerAdapter;
use Common\Data\Mapper\Lva\NewTmUser as NewTmUserMapper;
use Common\Data\Mapper\Lva\TransportManagerApplication;
use Common\Data\Mapper\Lva\TransportManagerApplication as TransportManagerApplicationMapper;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query\User\UserSelfserve;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Transport Managers Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTransportManagersController extends AbstractController
{
    use Traits\CrudTableTrait;

    protected $section = 'transport_managers';

    protected $lva = 'application';

    protected string $location = 'external';

    /** @var  AbstractTransportManagerAdapter */
    protected $adapter;

    protected string $baseRoute = 'lva-%s/transport_managers';

    /** @var  \Common\Service\Helper\FormHelperService */
    protected $hlpForm;

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    protected TableFactory $tableFactory;

    protected QuerySender $querySender;
    /**
     * @param $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        protected FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        protected ScriptFactory $scriptFactory,
        protected QueryService $queryService,
        protected CommandService $commandService,
        protected AnnotationBuilder $transferAnnotationBuilder,
        protected TransportManagerHelperService $transportManagerHelper,
        protected $lvaAdapter
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Transport Managers section
     *
     * @return array|\Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        $this->lvaAdapter->addMessages($this->getLicenceId());

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-transport_managers')
            ->getForm();

        $table = $this->lvaAdapter->getTable('lva-transport-managers-' . $this->location . '-' . $this->lva);
        $tableData = $this->lvaAdapter->getTableData($this->getIdentifier(), $this->getLicenceId());
        if ($tableData === null) {
            return $this->notFoundAction();
        }

        $table->loadData($tableData);
        $form->get('table')->get('table')->setTable($table);
        $form->get('table')->get('rows')->setValue(count($table->getRows()));

        $this->formServiceManager
            ->get('Lva\\' . ucfirst($this->lva))
            ->alterForm($form);

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->renderForm($form);
        }

        $data = (array)$request->getPost();
        $form->setData($data);

        // if is it not required to have at least one TM, then remove the validator
        if (!$this->lvaAdapter->mustHaveAtLeastOneTm()) {
            $form->getInputFilter()->remove('table');
        }

        $crudAction = $this->getCrudAction([$data['table']]);
        if ($crudAction !== null) {
            return $this->handleCrudAction($crudAction);
        }

        if ($form->isValid()) {
            if ($this->lva !== 'licence') {
                $data = ['id' => $this->getIdentifier(), 'section' => 'transportManagers'];
                $this->handleCommand(Command\Application\UpdateCompletion::create($data));
            }

            return $this->completeSection('transport_managers');
        }

        return $this->renderForm($form);
    }

    /**
     * Render Form
     *
     * @param \Laminas\Form\FormInterface $form Form
     *
     * @return \Common\View\Model\Section
     */
    protected function renderForm($form)
    {
        $this->scriptFactory->loadFile('lva-crud-delta');

        return $this->render('transport_managers', $form);
    }

    /**
     * Process action - add
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->getAddForm();

        if ($request->isPost()) {
            $formData = (array)$request->getPost();

            if (isset($formData['data']['addUser'])) {
                return $this->redirect()->toRoute(null, ['action' => 'addNewUser'], [], true);
            }

            $formData = (array)$request->getPost();
            $form->setData($formData);

            if ($form->isValid()) {
                return $this->redirect()->toRoute(
                    null,
                    ['action' => 'addTm', 'child_id' => $formData['data']['registeredUser']],
                    [],
                    true
                );
            }
        }

        return $this->render('add-transport_managers', $form);
    }

    /**
     * Process Action - addTm
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function addTmAction()
    {
        $childId = $this->params('child_id');

        $user = $this->getCurrentUser();

        // User has selected [him/her]self
        // So we don't need to continue to show the form
        if ($user['id'] == $childId) {
            $form = $this->getAddForm();

            $command = $this->transferAnnotationBuilder
                ->createCommand(
                    Command\TransportManagerApplication\Create::create(
                        ['application' => $this->getIdentifier(), 'user' => $childId, 'action' => 'A']
                    )
                );
            /* @var $response \Common\Service\Cqrs\Response */
            $response = $this->commandService->send($command);

            if ($response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            if ($response->isClientError()) {
                $errors = TransportManagerApplicationMapper::mapFromErrors($form, $response->getResult());

                foreach ($errors as $error) {
                    $this->flashMessengerHelper->addErrorMessage($error);
                }
            }

            if ($response->isOk()) {
                return $this->redirect()->toRouteAjax(
                    null,
                    [
                        'action' => 'details',
                        'child_id' => $response->getResult()['id']['transportManagerApplication']
                    ],
                    [],
                    true
                );
            }

            return $this->redirect()->toRoute(
                null,
                [
                    'action' => 'add',
                    'child_id' => null,
                ],
                [],
                true
            );
        }

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $query = $this->transferAnnotationBuilder
            ->createQuery(UserSelfserve::create(['id' => $childId]));

        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->queryService->send($query);
        $userDetails = $response->getResult();

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->getTmDetailsForm($userDetails['contactDetails']['emailAddress']);
        $formData = [
            'data' => [
                'forename' => $userDetails['contactDetails']['person']['forename'],
                'familyName' => $userDetails['contactDetails']['person']['familyName'],
                'email' => $userDetails['contactDetails']['emailAddress'],
                'birthDate' => $userDetails['contactDetails']['person']['birthDate'],
            ]
        ];

        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            unset($formData['data']['birthDate']);
            $formData = array_merge_recursive($postData, $formData);
        }

        $form->setData($formData);

        if ($request->isPost() && $form->isValid()) {
            $formData = $form->getData();

            // create TMA
            $command = Command\TransportManagerApplication\Create::create(
                [
                    'application' => $this->getIdentifier(),
                    'user' => $childId,
                    'action' => 'A',
                    'dob' => $formData['data']['birthDate'],
                ]
            );
            /* @var $response \Common\Service\Cqrs\Response */
            $response = $this->handleCommand($command);

            if ($response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            if ($response->isClientError()) {
                $errors = TransportManagerApplicationMapper::mapFromErrors($form, $response->getResult());

                foreach ($errors as $error) {
                    $this->flashMessengerHelper->addErrorMessage($error);
                }
            }

            if ($response->isOk()) {
                $this->flashMessengerHelper
                    ->addSuccessMessage('lva-tm-sent-success');

                return $this->redirect()->toRouteAjax(
                    $this->getBaseRoute(),
                    [
                        'action' => null
                    ],
                    [],
                    true
                );
            }
        }

        return $this->render('addTm-transport_managers', $form);
    }

    /**
     * Process action - addNewUser
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function addNewUserAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $formHelper = $this->formHelper;
        /** @var \Common\Form\Form $form */
        $form = $formHelper->createFormWithRequest('Lva\NewTmUser', $request);

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $hasEmail = $data['data']['hasEmail'] ?? null;

                $command = Command\Tm\CreateNewUser::create(
                    [
                        'application' => $this->getIdentifier(),
                        'firstName' => $data['data']['forename'],
                        'familyName' => $data['data']['familyName'],
                        'birthDate' => $data['data']['birthDate'],
                        'hasEmail' => $hasEmail,
                        'username' => $data['data']['username'],
                        'emailAddress' => $data['data']['emailAddress'],
                        'translateToWelsh' => $data['data']['translateToWelsh'],
                    ]
                );

                $response = $this->handleCommand($command);

                $fm = $this->flashMessengerHelper;

                if ($response->isOk()) {
                    $successMessage = $hasEmail === 'Y' ? 'tm-add-user-success-message' : 'tm-add-user-success-message-no-email';
                    $fm->addSuccessMessage($successMessage);
                    return $this->redirect()->toRouteAjax($this->getBaseRoute(), ['action' => null], [], true);
                }

                if ($response->isServerError()) {
                    $fm->addCurrentUnknownError();
                } else {
                    $messages = $response->getResult()['messages'];

                    NewTmUserMapper::mapFormErrors($form, $messages, $fm);
                }
            }
        }

        $this->scriptFactory->loadFile('lva-tm-add-user');

        return $this->render('add-transport_managers', $form);
    }

    /**
     * Get Transport Manager Details Form
     *
     * @param string $email E-mail
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getTmDetailsForm($email)
    {
        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formHelper
            ->createFormWithRequest('Lva\AddTransportManagerDetails', $this->getRequest());

        $form->get('data')->get('guidance')->setTokens([$email]);

        return $form;
    }

    /**
     * Get Add Form
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getAddForm()
    {
        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formHelper
            ->createFormWithRequest('Lva\AddTransportManager', $this->getRequest());

        $orgId = $this->getCurrentOrganisationId();

        $registeredUsers = $this->getOrganisationUsersForSelect($orgId);

        $form->get('data')->get('registeredUser')->setEmptyOption('Please select');
        $form->get('data')->get('registeredUser')->setValueOptions($registeredUsers);

        return $form;
    }

    /**
     * Get users in organisation for use in a select element
     *
     * @param int $organisationId Organisation Id
     *
     * @return array
     */
    protected function getOrganisationUsersForSelect($organisationId)
    {
        $query = $this->transferAnnotationBuilder
            ->createQuery(\Dvsa\Olcs\Transfer\Query\User\UserList::create(['organisation' => $organisationId]));
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->queryService->send($query);
        $options = [];
        foreach ($response->getResult()['results'] as $user) {
            $name = $user['contactDetails']['person']['forename'] . ' ' . $user['contactDetails']['person']['familyName'];
            if (trim($name) === '' || trim($name) === '0') {
                $name = 'User ID ' . $user['id'];
            }

            $options[$user['id']] = $name;
        }

        asort($options);

        return $options;
    }

    /**
     * Handle CrudTableTrait delete
     *
     * @return void
     */
    protected function delete()
    {
        // get ids to delete
        $ids = explode(',', $this->params('child_id'));

        $this->lvaAdapter->delete($ids, $this->getIdentifier());
    }

    /**
     * Override the delete title.
     *
     * @return string The modal message key.
     */
    protected function getDeleteTitle()
    {
        return 'delete-tm';
    }

    /**
     * Override the delete title.
     *
     * @return string The modal message key.
     */
    protected function getDeleteMessage()
    {
        if ($this->isLastTmLicence()) {
            return 'delete.final-tm.confirmation.text';
        }

        return 'delete.confirmation.text';
    }

    /**
     * Checks if number of Tm's on licence = 1
     *
     * @return bool
     */
    protected function isLastTmLicence()
    {
        return $this->lva === 'licence'
        && $this->lvaAdapter->getNumberOfRows($this->getIdentifier(), $this->getLicenceId()) === 1;
    }

    /**
     * Restore Transport Managers
     *
     * @return \Laminas\Http\Response
     */
    public function restoreAction()
    {
        $ids = explode(',', $this->params('child_id'));

        // get table data
        $data = $this->lvaAdapter->getTableData($this->getIdentifier(), $this->getLicenceId());

        $tmaIdsToDelete = [];
        foreach ($ids as $id) {
            if (str_starts_with($id, 'L')) {
                $tmaId = $this->findTmaId($data, $id);
                $tmaIdsToDelete[] = $tmaId;
            } else {
                // add TMA ID to delete array
                $tmaIdsToDelete[] = $id;
            }
        }
        $command = $this->transferAnnotationBuilder
            ->createCommand(
                Command\TransportManagerApplication\Delete::create(
                    ['ids' => array_unique($tmaIdsToDelete)]
                )
            );
        $this->commandService->send($command);

        return $this->redirect()->toRouteAjax($this->getBaseRoute(), [], [], true);
    }

    /**
     * Find the Transport Manager application ID that is linked to Transport Manager application ID
     *
     * @param array  $data  Data
     * @param string $tmlId This is the TML ID prefixed with an "L"
     *
     * @return int|false The TMA ID or false if not found
     */
    protected function findTmaId($data, $tmlId)
    {
        foreach ($data as $tmId => $row) {
            if ($row['id'] === $tmlId) {
                return $data[$tmId . 'a']['id'];
            }
        }

        return false;
    }
}
