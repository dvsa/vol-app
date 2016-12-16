<?php

namespace Olcs\Controller;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Cqrs\Response;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Olcs\Listener\CrudListener;
use Olcs\Logging\Log\Logger;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Olcs\Mvc\Controller\ParameterProvider\ParameterProviderInterface;
use Olcs\Mvc\Controller\Plugin;
use Olcs\View\Builder\BuilderInterface as ViewBuilderInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;

/**
 * Abstract class to extend for BASIC list/edit/delete functions
 *
 * @TODO Find another method for ALTER FORM... this method is crazy!
 * @TODO define post add/edit/delete redirect location as a parameter?
 * @TODO review navigation stuff...
 *
 * @method ViewBuilderInterface viewBuilder()
 * @method Plugin\Script script()
 * @method Plugin\Placeholder placeholder()
 * @method Plugin\Table table()
 * @method Response handleQuery(QueryInterface $query)
 * @method Response handleCommand(CommandInterface $query)
 * @method \Common\Controller\Plugin\Redirect redirect()
 * @method Plugin\Confirm confirm($string)
 */
abstract class AbstractInternalController extends AbstractActionController
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = '';

    /**
     * Array of scripts, any scripts included in the array will be added for all actions
     * scripts can be included on a per action basis by defining the action name as a key mapping to an array of scripts
     * eg: ['global', 'deleteAction' => ['delete-script']]
     *
     * @var array
     */
    protected $inlineScripts = [];

    /**
     * Array of additional scripts, any scripts included in the array will be added for all actions
     * scripts can be included on a per action basis by defining the action name as a key mapping to an array of scripts
     * eg: ['global', 'deleteAction' => ['delete-script']]
     *
     * @var array
     */
    protected $additionalScripts = [];

    /**
     * @var array
     */
    protected $scriptFiles = [];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     *
     * both listvars and itemParams are an array of route params that are used for various operations
     * you can either specify an item (if the param name in the dto is the same as the route param or
     * you can specify a key => value pair to map route param (value) to dto param (key)
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table';
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'DESC';
    protected $tableName = '';
    protected $listDto;
    protected $listVars = [];
    protected $filterForm = '';

    /**
     * Variables for controlling details view rendering
     * details view template and itemDto are required.
     */
    protected $detailsViewTemplate = '';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto;
    protected $itemParams = ['id'];
    protected $detailsContentTitle;

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     *
     * @var string $formClass This now represents the add or edit form, that is unless there's an $addFormClass
     * @var string|MapperInterface $mapperClass
     */
    protected $formClass = '';
    protected $updateCommand;
    protected $mapperClass;

    /**
     * Form class for add form. If this has a value, then this will be used, otherwise $formClass will be used.
     */
    protected $addFormClass = '';
    protected $addContentTitle = 'Add';
    protected $addSuccessMessage = 'Created record';

    /**
     * Custom view template for add / edit form
     */
    protected $editViewTemplate = 'pages/crud-form';
    protected $editContentTitle = 'Edit';
    protected $editSuccessMessage = 'Updated record';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     * @var string|CommandInterface
     */
    protected $createCommand;

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => AddFormDefaultData::FROM_ROUTE means get value from route,
     * see conviction controller
     *
     * @var array
     */
    protected $defaultData = [];

    protected $routeIdentifier = 'id';

    /**
     * Defines additional allowed POST actions
     *
     * Format is action => config array
     * see OppositionController
     *
     * @var array
     */
    protected $crudConfig = [];

    protected $persist = true;

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteParams = ['id'];
    protected $deleteCommand;
    protected $deleteModalTitle = 'Delete record';
    protected $deleteConfirmMessage = 'Are you sure you want to permanently delete the selected record(s)?';
    protected $deleteSuccessMessage = 'Record deleted';
    protected $hasMultiDelete = false;

    /**
     * Variables for controlling the close action.
     * Command is required, as are itemParams from above
     */
    protected $closeParams = ['id'];
    protected $closeCommand;
    protected $closeModalTitle = 'Close';
    protected $closeConfirmMessage = 'Are you sure you want to close the selected record(s)?';
    protected $closeSuccessMessage = 'Record closed';
    protected $hasMultiClose = false;

    /**
     * Variables for controlling the reopen action.
     * Command is required, as are itemParams from above
     */
    protected $reopenParams = ['id'];
    protected $reopenCommand;
    protected $reopenModalTitle = 'Reopen';
    protected $reopenConfirmMessage = 'Are you sure you want to reopen the selected record(s)?';
    protected $reopenSuccessMessage = 'Record reopened';
    protected $hasMultiReopen = false;

    /**
     * Allows override of default behaviour for redirects. See Case Overview Controller
     *
     * @var array
     */
    protected $redirectConfig = [];

    /**
     * @var string
     *
     * Form to use for the comments box
     */
    protected $commentFormClass;

    /**
     * @var string|QueryInterface
     *
     * DTO to retrieve comment box data, likely to be case
     */
    protected $commentItemDto;

    /**
     * @var array
     *
     * Comment box item params
     */
    protected $commentItemParams;

    /**
     * @var string|CommandInterface
     *
     * Comment box update command
     */
    protected $commentUpdateCommand;

    /**
     * @var string|MapperInterface
     *
     * Comment box mapper class
     */
    protected $commentMapperClass;

    /**
     * @var string
     *
     * On pages with a comment form, we need to override the title returned from editAction
     */
    protected $commentTitle;

    /**
     * Caches the list data result
     * @var array
     */
    protected $listData;

    /**
     * Gets a comment box
     *
     * @return array|ViewModel
     */
    public function getCommentBox()
    {
        $commentBox = $this->edit(
            $this->commentFormClass,
            $this->commentItemDto,
            new GenericItem($this->commentItemParams),
            $this->commentUpdateCommand,
            $this->commentMapperClass
        );

        //must be set after edit has been run and not before
        $this->placeholder()->setPlaceholder('contentTitle', $this->commentTitle);

        return $commentBox;
    }

    public function indexAction()
    {
        if (null !== $this->commentItemDto) {
            $commentBox = $this->getCommentBox();

            if ($commentBox instanceof HttpResponse) {
                return $commentBox;
            }
        }

        return $this->index(
            $this->listDto,
            new GenericList($this->listVars, $this->defaultTableSortField, $this->defaultTableOrderField),
            $this->tableViewPlaceholderName,
            $this->tableName,
            $this->tableViewTemplate,
            $this->filterForm
        );
    }

    public function detailsAction()
    {
        return $this->details(
            $this->itemDto,
            new GenericItem($this->itemParams),
            $this->detailsViewPlaceholderName,
            $this->detailsViewTemplate,
            $this->detailsContentTitle
        );
    }

    public function addAction()
    {
        return $this->add(
            !empty($this->addFormClass) ? $this->addFormClass : $this->formClass,
            new AddFormDefaultData($this->defaultData),
            $this->createCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            $this->addSuccessMessage,
            $this->addContentTitle
        );
    }

    public function editAction()
    {
        return $this->edit(
            $this->formClass,
            $this->itemDto,
            new GenericItem($this->itemParams),
            $this->updateCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            $this->editSuccessMessage,
            $this->editContentTitle
        );
    }

    public function deleteAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->deleteParams, $this->hasMultiDelete),
            $this->deleteCommand,
            $this->deleteModalTitle,
            $this->deleteConfirmMessage,
            $this->deleteSuccessMessage
        );
    }

    /**
     * Closes an entity
     *
     * @return array|mixed|ViewModel
     */
    public function closeAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->closeParams, $this->hasMultiClose),
            $this->closeCommand,
            $this->closeModalTitle,
            $this->closeConfirmMessage,
            $this->closeSuccessMessage
        );
    }

    /**
     * Reopens an entity
     *
     * @return array|mixed|ViewModel
     */
    public function reopenAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->reopenParams, $this->hasMultiReopen),
            $this->reopenCommand,
            $this->reopenModalTitle,
            $this->reopenConfirmMessage,
            $this->reopenSuccessMessage
        );
    }

    /**
     * @param string|QueryInterface      $listDto
     * @param ParameterProviderInterface $paramProvider
     * @param string                     $tableViewPlaceholderName
     * @param string                     $tableName
     * @param string                     $tableViewTemplate
     * @param string                     $filterForm
     *
     * @return array|ViewModel
     */
    final protected function index(
        $listDto,
        ParameterProviderInterface $paramProvider,
        $tableViewPlaceholderName,
        $tableName,
        $tableViewTemplate,
        $filterForm = ''
    ) {
        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        $paramProvider->setParams($this->plugin('params'));
        $listParams = $paramProvider->provideParameters();
        $response = $this->handleQuery($listDto::create($listParams));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isOk()) {
            $data = $response->getResult();
            $this->listData = $data;

            $table = $this->table()->buildTable($tableName, $data, $listParams);

            $table = $this->alterTable($table, $data);

            /**
             * @todo in some cases we only care about putting the table into this placeholder, we then don't care
             * about constructing a view, so maybe we need a wa
             */
            $this->placeholder()->setPlaceholder(
                $tableViewPlaceholderName,
                $table->render()
            );

        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->handleErrors($response->getResult());
        }

        if ($filterForm !== '') {
            /* @var \Zend\Form\Form $form */
            $form = $this->getForm($filterForm);
            $form->remove('csrf');
            $form->remove('security');
            $form->setData($this->params()->fromQuery());
            $this->placeholder()->setPlaceholder('tableFilters', $form);
        }

        return $this->viewBuilder()->buildViewFromTemplate($tableViewTemplate);
    }

    /**
     * @param string|QueryInterface      $itemDto
     * @param ParameterProviderInterface $paramProvider
     * @param string                     $detailsViewPlaceHolderName
     * @param string                     $detailsViewTemplate
     * @param string|null                $contentTitle
     *
     * @return array|ViewModel
     */
    final protected function details(
        $itemDto,
        ParameterProviderInterface $paramProvider,
        $detailsViewPlaceHolderName,
        $detailsViewTemplate,
        $contentTitle = null
    ) {
        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        $this->placeholder()->setPlaceholder('contentTitle', $contentTitle);

        $paramProvider->setParams($this->plugin('params'));
        $params = $paramProvider->provideParameters();

        $query = $itemDto::create($params);

        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                $this->placeholder()->setPlaceholder($detailsViewPlaceHolderName, $data);
            }

        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->handleErrors($response->getResult());
        }

        return $this->viewBuilder()->buildViewFromTemplate($detailsViewTemplate);
    }

    /**
     * Flow for this method:
     * 1. Get a form.
     * 2. Generate initial form values; map them to form fields
     * 3. Call any existing alter form method
     * 4. Set initial data to form
     * 5. Process postcode lookup
     *
     * If post:
     * 1. Do above
     * 2. Set post data into form
     * 3. If valid, merge initial data with FILTERED values from form
     * 4. Map data into command
     * 5. Send command + handle result
     *
     * @param string                     $formClass
     * @param ParameterProviderInterface $defaultDataProvider
     * @param string|CommandInterface    $createCommand
     * @param string|MapperInterface     $mapperClass
     * @param string                     $editViewTemplate
     * @param string                     $successMessage
     * @param string|null                $contentTitle
     *
     * @return mixed|ViewModel
     */
    final protected function add(
        $formClass,
        ParameterProviderInterface $defaultDataProvider,
        $createCommand,
        $mapperClass,
        $editViewTemplate = 'pages/crud-form',
        $successMessage = 'Created record',
        $contentTitle = null
    ) {
        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        $defaultDataProvider->setParams($this->plugin('params'));

        $action = ucfirst($this->params()->fromRoute('action'));

        /** @var \Common\Form\Form $form */
        $form = $this->getForm($formClass);
        $initialData = $mapperClass::mapFromResult($defaultDataProvider->provideParameters());

        if (method_exists($this, 'alterFormFor' . $action)) {
            $form = $this->{'alterFormFor' . $action}($form, $initialData);
        }

        $form->setData($initialData);
        $this->placeholder()->setPlaceholder('form', $form);
        $this->placeholder()->setPlaceholder('contentTitle', $contentTitle);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData((array)$this->params()->fromPost());
        }

        $hasProcessed =
            $this->getServiceLocator()->get('Helper\Form')->processAddressLookupForm($form, $this->getRequest());

        if (!$hasProcessed && $this->persist && $request->isPost() && $form->isValid()) {
            $data = ArrayUtils::merge($initialData, $form->getData());
            $commandData = $mapperClass::mapFromForm($data);
            $response = $this->handleCommand($createCommand::create($commandData));

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($successMessage);
                $formActions = $this->params()->fromPost('form-actions');

                if (isset($formActions['addAnother'])) {
                    $this->redirectRefresh();
                }

                return $this->redirectTo($response->getResult());

            } elseif ($response->isClientError()) {
                $flashErrors = $mapperClass::mapFromErrors($form, $response->getResult());

                foreach ($flashErrors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }

            } elseif ($response->isServerError()) {
                $this->handleErrors($response->getResult());
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($editViewTemplate);
    }

    /**
     * @param string                     $formClass
     * @param string|QueryInterface      $itemDto
     * @param ParameterProviderInterface $paramProvider
     * @param string|CommandInterface    $updateCommand
     * @param string|MapperInterface     $mapperClass
     * @param string                     $editViewTemplate
     * @param string                     $successMessage
     * @param null                       $contentTitle
     *
     * @return array|ViewModel
     * @internal param $paramNames
     */
    final protected function edit(
        $formClass,
        $itemDto,
        ParameterProviderInterface $paramProvider,
        $updateCommand,
        $mapperClass,
        $editViewTemplate = 'pages/crud-form',
        $successMessage = 'Updated record',
        $contentTitle = null
    ) {
        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $action = ucfirst($this->params()->fromRoute('action'));
        $form = $this->getForm($formClass);
        $this->placeholder()->setPlaceholder('form', $form);
        $this->placeholder()->setPlaceholder('contentTitle', $contentTitle);

        if ($request->isPost()) {
            $dataFromPost = (array)$this->params()->fromPost();
            $form->setData($dataFromPost);

            if (method_exists($this, 'alterFormFor' . $action)) {
                $form = $this->{'alterFormFor' . $action}($form, $dataFromPost);
            }
        }

        $hasProcessed =
            $this->getServiceLocator()->get('Helper\Form')->processAddressLookupForm($form, $this->getRequest());

        if (!$hasProcessed && $this->persist && $request->isPost() && $form->isValid()) {
            $commandData = $mapperClass::mapFromForm($form->getData());
            $response = $this->handleCommand($updateCommand::create($commandData));

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($successMessage);
                return $this->redirectTo($response->getResult());

            } elseif ($response->isClientError()) {
                $flashErrors = $mapperClass::mapFromErrors($form, $response->getResult());

                foreach ($flashErrors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }

            } elseif ($response->isServerError()) {
                $this->handleErrors($response->getResult());
            }

        } elseif (!$request->isPost()) {
            $paramProvider->setParams($this->plugin('params'));
            $itemParams = $paramProvider->provideParameters();
            $response = $this->handleQuery($itemDto::create($itemParams));

            if (!$response instanceof Response || $response->isNotFound()) {
                return $this->notFoundAction();
            }

            if ($response->isOk()) {
                $result = $response->getResult();

                $formData = $mapperClass::mapFromResult($result);

                if (method_exists($this, 'alterFormFor' . $action)) {
                    $form = $this->{'alterFormFor' . $action}($form, $formData);
                }

                $form->setData($formData);

            } elseif ($response->isClientError() || $response->isServerError()) {
                $this->handleErrors($response->getResult());
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($editViewTemplate);
    }

    /**
     * Handle single delete and multiple delete as well
     *
     * @param ParameterProviderInterface $paramProvider
     * @param string|CommandInterface    $confirmCommand
     * @param string                     $modalTitle
     * @param string                     $confirmMessage
     * @param string                     $successMessage
     *
     * @return array|mixed|ViewModel
     */
    final protected function confirmCommand(
        ParameterProviderInterface $paramProvider,
        $confirmCommand,
        $modalTitle,
        $confirmMessage,
        $successMessage
    ) {
        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        $paramProvider->setParams($this->plugin('params'));
        $params = $paramProvider->provideParameters();

        $confirm = $this->confirm($confirmMessage);

        if ($confirm instanceof ViewModel) {
            $this->placeholder()->setPlaceholder('pageTitle', $modalTitle);
            return $this->viewBuilder()->buildView($confirm);
        }

        $response = $this->handleCommand($confirmCommand::create($params));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($successMessage);

        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->handleErrors($response->getResult());
        }

        return $this->redirectTo($response->getResult());
    }

    /**
     * Handles errors and displays all messages with translation
     *
     * @param array $restResponse
     * @return void
     */
    public function handleErrors(array $restResponse)
    {
        $errors = [];

        if (!empty($restResponse['messages']) && is_array($restResponse['messages'])) {
            $translator = $this->getServiceLocator()->get('Translator');

            foreach ($restResponse['messages'] as $message) {
                if (!empty($message) && is_string($message) && ($message !== $translator->translate($message))) {
                    // display only error which has translation
                    $errors[] = $message;
                }
            }
        }

        if (empty($errors)) {
            // or display generic error message
            $errors = ['unknown-error'];

            Logger::debug(__FILE__);
            Logger::debug(__METHOD__);
            Logger::debug('unknown-error', $restResponse);
        }

        foreach ($errors as $errorMessage) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($errorMessage);
        }
    }

    /**
     * Processes a command, and populates flash messages for the user
     *
     * @param ParameterProviderInterface $paramProvider
     * @param string|CommandInterface    $command
     * @param string                     $successMessage
     *
     * @return array|mixed
     */
    final protected function processCommand(
        ParameterProviderInterface $paramProvider,
        $command,
        $successMessage = 'Update successful'
    ) {
        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        $paramProvider->setParams($this->plugin('params'));
        $params = $paramProvider->provideParameters();

        $response = $this->handleCommand($command::create($params));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($successMessage);

        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->handleErrors($response->getResult());
        }

        return $this->redirectTo($response->getResult());
    }

    /**
     * @param array $restResponse
     * @return array
     */
    public function redirectConfig(array $restResponse)
    {
        $action = $this->params()->fromRoute('action', null);
        $action = strtolower($action);

        // intercept cancelled forms to allow alternative redirect config
        if ($this->hasCancelledForm() && isset($this->redirectConfig['cancel'])) {
            $action = 'cancel';
        }

        if (!isset($this->redirectConfig[$action])) {
            return[];
        }

        $params = [];

        $config = $this->redirectConfig[$action];

        //overrides the default action - case overview controller would use "details" here
        if (isset($config['action'])) {
            $params['action'] = $config['action'];
        }

        //allows us to reuse some route params but not others (with reUseParams set to false)
        if (isset($config['routeMap'])) {
            foreach ($config['routeMap'] as $routeIdentifier => $routeParam) {
                $params[$routeIdentifier] = $this->params()->fromRoute($routeParam, null);
            }
        }

        if (isset($config['resultIdMap'])) {
            foreach ($config['resultIdMap'] as $routeIdentifier => $idParam) {
                if (isset($restResponse['id'][$idParam])) {
                    $params[$routeIdentifier] = $restResponse['id'][$idParam];
                }
            }
        }

        $redirect = [];

        //if we're overriding the default route
        if (isset($config['route'])) {
            $redirect['route'] = $config['route'];
        }

        //if we're adding params
        if (!empty($params)) {
            $redirect['params'] = $params;
        }

        //whether we're reusing params
        if (isset($config['reUseParams'])) {
            $redirect['reUseParams'] = $config['reUseParams'];
        }

        //  options
        if (isset($config['options'])) {
            $redirect['options'] = $config['options'];
        }

        return $redirect;
    }

    /**
     * @param array $restResponse
     * @return mixed
     */
    public function redirectTo(array $restResponse)
    {
        $extraConfig = $this->redirectConfig($restResponse);

        $defaults = [
            'route' => null,
            'params' => [
                'action' => 'index',
                $this->routeIdentifier => null, // ID Not required for index.
            ],
            'options' => [
                'code' => '303',
                'query' => $this->getRequest()->getQuery()->toArray(),
            ],
            'reUseParams' => true,
        ];

        $routeParams = ArrayUtils::merge($defaults, $extraConfig);

        return $this->redirect()->toRouteAjax(
            $routeParams['route'],
            $routeParams['params'],
            $routeParams['options'],
            $routeParams['reUseParams']
        );
    }

    /**
     * Refreshes the page with the same action, used for things like "add another"
     *
     * @return HttpResponse
     */
    public function redirectRefresh()
    {
        return $this->redirect()->toRoute(null, [], ['code' => '303'], true);
    }

    /**
     * @codeCoverageIgnore this is part of the event system.
     */
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        $listener = new CrudListener($this, $this->routeIdentifier, $this->crudConfig);
        $this->getEventManager()->attach($listener);

        if (method_exists($this, 'setNavigationCurrentLocation')) {
            $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'setNavigationCurrentLocation'), 6);
        }

        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'attachScripts'), -100);
    }

    final public function attachScripts(MvcEvent $event)
    {
        $action = static::getMethodFromAction($event->getRouteMatch()->getParam('action', 'not-found'));
        $scripts = $this->getInlineScripts($action);

        $this->script()->addScripts($scripts);

        $scripts = $this->getScriptFiles($action);

        $this->script()->appendScriptFiles($scripts);

    }

    private function getInlineScripts($action)
    {
        $scripts = [];
        if (isset($this->inlineScripts[$action])) {
            $scripts = array_merge($scripts, $this->inlineScripts[$action]);
        }

        $callback = function ($item) {
            return !is_array($item);
        };
        $globalScripts = array_filter($this->inlineScripts, $callback);

        return array_merge($scripts, $globalScripts);
    }

    /**
     * Intercepts form posts that have been cancelled in order to set the action to cancelled and override the redirect.
     *
     * @return bool true if cancelled
     */
    private function hasCancelledForm()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return false;
        }

        $postData = (array)$request->getPost();

        return isset($postData['form-actions']['cancel']);
    }

    /**
     * Returns an array of script files to add to the page (not inline)
     * either specific to the action being dispatched, or all actions
     *
     * @param $action
     * @return array
     */
    private function getScriptFiles($action)
    {
        $scripts = [];
        if (isset($this->scriptFiles[$action])) {
            $scripts = array_merge($scripts, $this->scriptFiles[$action]);
        }

        $callback = function ($item) {
            return !is_array($item);
        };
        $globalScripts = array_filter($this->scriptFiles, $callback);

        return array_merge($scripts, $globalScripts);
    }

    /**
     * Sets the navigation to that specified in the controller. Useful for when a controller is
     * 100% represented by a single navigation object.
     *
     * @see $this->navigationId
     *
     * @return boolean true
     */
    final public function setNavigationCurrentLocation()
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        if (!empty($this->navigationId)) {
            $navigation->findOneBy('id', $this->navigationId)->setActive();
        }

        return true;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getForm($name)
    {
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm($name);
        $this->getServiceLocator()->get('Helper\Form')->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Override in derived classes to alter table *presentation* based on the
     * list data
     *
     * @param TableBuilder $table
     * @param array $data
     * @return TableBuilder
     */
    protected function alterTable($table, $data)
    {
        return $table;
    }

    /**
     * Check if a button was pressed. Part of original AbstractActionController
     *
     * @param string $button
     * @param array $data
     * @return bool
     */
    public function isButtonPressed($button, $data = null)
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if (is_null($data)) {
            $data = (array)$request->getPost();
        }

        return $request->isPost() && isset($data['form-actions'][$button]);
    }

    /**
     * Update table action with query
     *
     * @param TableBuilder $table
     */
    protected function updateTableActionWithQuery($table)
    {
        $query = $this->getRequest()->getUri()->getQuery();
        $action = $table->getVariable('action');
        if ($query && strpos('?', $action) === false) {
            $action .= '?' . $query;
            $table->setVariable('action', $action);
        }
    }
}
