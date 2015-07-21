<?php
/**
 * History Controller
 */
namespace Olcs\Controller;

use Olcs\Listener\CrudListener;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent as MvcEvent;
use Olcs\Logging\Log\ZendLogPsr3Adapter as Logger;

// for type hints
use Olcs\View\Builder\BuilderInterface as ViewBuilderInterface;
use Olcs\Mvc\Controller\Plugin;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Common\Service\Cqrs\Response;
use Zend\Http\Response as HttpResponse;

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
 * @method Response handleCommand(QueryInterface $query)
 * @method Plugin\Confirm confirm($string)
 */
abstract class AbstractInternalController extends AbstractActionController
{
    const FROM_ROUTE = 'route';
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
    protected $tableViewTemplate = 'partials/table';
    protected $defaultTableSortField = 'id';
    protected $tableName = '';
    protected $listDto = '';
    protected $listVars = [];
    protected $filterForm = '';

    /**
     * Variables for controlling details view rendering
     * details view template and itemDto are required.
     */
    protected $detailsViewTemplate = '';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = '';
    protected $itemParams = ['id'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     *
     * @var string $formClass This now represents the add or edit form, that is unless there's an $addFormClass
     */
    protected $formClass = '';
    protected $updateCommand = '';
    protected $mapperClass = '';

    /**
     * Form class for add form. If this has a value, then this will be used, otherwise $formClass will be used.
     */
    protected $addFormClass = '';

    /**
     * Custom view template for add / edit form
     */
    protected $editViewTemplate = 'pages/crud-form';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = '';

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => static::FROM_ROUTE means get value from route,
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

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = '';
    protected $deleteModalTitle = 'internal.delete-action-trait.title';

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
     * @var string
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
     * @var string
     *
     * Comment box update command
     */
    protected $commentUpdateCommand;

    /**
     * @var string
     *
     * Comment box mapper class
     */
    protected $commentMapperClass;

    public function indexAction()
    {
        if (!empty($this->commentItemDto)) {
            $commentBox = $this->edit(
                $this->commentFormClass,
                $this->commentItemDto,
                $this->commentItemParams,
                $this->commentUpdateCommand,
                $this->commentMapperClass
            );

            if ($commentBox instanceof HttpResponse) {
                return $commentBox;
            }
        }

        return $this->index(
            $this->listDto,
            $this->listVars,
            $this->defaultTableSortField,
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
            $this->itemParams,
            $this->detailsViewPlaceholderName,
            $this->detailsViewTemplate
        );
    }

    public function addAction()
    {
        return $this->add(
            !empty($this->addFormClass) ? $this->addFormClass : $this->formClass,
            $this->defaultData,
            $this->createCommand,
            $this->mapperClass,
            $this->editViewTemplate
        );
    }

    public function editAction()
    {
        return $this->edit(
            $this->formClass,
            $this->itemDto,
            $this->itemParams,
            $this->updateCommand,
            $this->mapperClass,
            $this->editViewTemplate
        );
    }

    public function deleteAction()
    {
        return $this->delete(
            $this->itemParams,
            $this->itemDto,
            $this->deleteCommand,
            $this->deleteModalTitle
        );
    }

    final protected function index(
        $listDto,
        $paramNames,
        $defaultSort,
        $tableViewPlaceholderName,
        $tableName,
        $tableViewTemplate,
        $filterForm = ''
    ) {
        $this->getLogger()->debug(__FILE__);
        $this->getLogger()->debug(__METHOD__);

        $listParams = $this->getListParams($paramNames, $defaultSort);
        $response = $this->handleQuery($listDto::create($listParams));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            $this->placeholder()->setPlaceholder(
                $tableViewPlaceholderName,
                $this->table()->buildTable($tableName, $data, $listParams)->render()
            );
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

    final protected function details($itemDto, $paramNames, $detailsViewPlaceHolderName, $detailsViewTemplate)
    {
        $this->getLogger()->debug(__FILE__);
        $this->getLogger()->debug(__METHOD__);

        $params = $this->getItemParams($paramNames);

        $query = $itemDto::create($params);

        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                $this->placeholder()->setPlaceholder($detailsViewPlaceHolderName, $data);
            }
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
     * @param $formClass
     * @param $defaultData
     * @param $createCommand
     * @param $mapperClass
     * @return mixed|ViewModel
     */
    final protected function add(
        $formClass,
        $defaultData,
        $createCommand,
        $mapperClass,
        $editViewTemplate = 'pages/crud-form',
        $successMessage = 'Created record'
    ) {
        $this->getLogger()->debug(__FILE__);
        $this->getLogger()->debug(__METHOD__);

        $action = ucfirst($this->params()->fromRoute('action'));
        $form = $this->getForm($formClass);
        $initialData = $mapperClass::mapFromResult($this->getDefaultFormData($defaultData));

        if (method_exists($this, 'alterFormFor' . $action)) {
            $form = $this->{'alterFormFor' . $action}($form, $initialData);
        }

        $form->setData($initialData);
        $this->placeholder()->setPlaceholder('form', $form);

        if ($this->getRequest()->isPost()) {
            $form->setData((array) $this->params()->fromPost());
        }

        $hasProcessed =
            $this->getServiceLocator()->get('Helper\Form')->processAddressLookupForm($form, $this->getRequest());

        if (!$hasProcessed && $this->getRequest()->isPost() && $form->isValid()) {
            $data = ArrayUtils::merge($initialData, $form->getData());
            $commandData = $mapperClass::mapFromForm($data);
            $response = $this->handleCommand($createCommand::create($commandData));

            if ($response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isClientError()) {
                $flashErrors = $mapperClass::mapFromErrors($form, $response->getResult());

                foreach ($flashErrors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }
            }

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($successMessage);
                return $this->redirectTo($response->getResult());
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($editViewTemplate);
    }

    /**
     * @param $formClass
     * @param $itemDto
     * @param $paramNames
     * @param $updateCommand
     * @param \Olcs\Data\Mapper\GenericFields $mapperClass
     * @return array|ViewModel
     */
    final protected function edit(
        $formClass,
        $itemDto,
        $paramNames,
        $updateCommand,
        $mapperClass,
        $editViewTemplate = 'pages/crud-form',
        $successMessage = 'Updated record'
    ) {
        $this->getLogger()->debug(__FILE__);
        $this->getLogger()->debug(__METHOD__);

        $request = $this->getRequest();
        $action = ucfirst($this->params()->fromRoute('action'));
        $form = $this->getForm($formClass);
        $this->placeholder()->setPlaceholder('form', $form);

        if ($request->isPost()) {
            $dataFromPost = (array) $this->params()->fromPost();
            $form->setData($dataFromPost);

            if (method_exists($this, 'alterFormFor' . $action)) {
                $form = $this->{'alterFormFor' . $action}($form, $dataFromPost);
            }
        }

        $hasProcessed =
            $this->getServiceLocator()->get('Helper\Form')->processAddressLookupForm($form, $this->getRequest());

        if (!$hasProcessed && $request->isPost() && $form->isValid()) {
            $commandData = $mapperClass::mapFromForm($form->getData());
            $response = $this->handleCommand($updateCommand::create($commandData));

            if ($response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isClientError()) {
                $flashErrors = $mapperClass::mapFromErrors($form, $response->getResult());

                foreach ($flashErrors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }
            }

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($successMessage);
                return $this->redirectTo($response->getResult());
            }
        } elseif (!$request->isPost()) {
            $itemParams = $this->getItemParams($paramNames);
            $response = $this->handleQuery($itemDto::create($itemParams));

            if ($response->isNotFound()) {
                return $this->notFoundAction();
            }

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isOk()) {
                $result = $response->getResult();
                $formData = $mapperClass::mapFromResult($result);

                if (method_exists($this, 'alterFormFor' . $action)) {
                    $form = $this->{'alterFormFor' . $action}($form, $formData);
                }

                $form->setData($formData);
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($editViewTemplate);
    }

    final protected function delete($paramNames, $itemDto, $deleteCommand, $modalTitle)
    {
        $this->getLogger()->debug(__FILE__);
        $this->getLogger()->debug(__METHOD__);

        $response = $this->handleQuery($itemDto::create($this->getItemParams($paramNames)));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return $this->redirectTo($response->getResult());
        }

        $data = $response->getResult();

        // Ok, now we're happy that we're deleting a record that actually exists..

        $confirm = $this->confirm(
            'Are you sure you want to permanently delete the selected record(s)?'
        );

        if ($confirm instanceof ViewModel) {
            $this->placeholder()->setPlaceholder('pageTitle', $modalTitle);
            return $this->viewBuilder()->buildView($confirm);
        }

        /** @var \Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand $deleteCommand */
        $response = $this->handleCommand($deleteCommand::create($data));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('Deleted record');
        }

        return $this->redirectTo($response->getResult());
    }

    private function getListParams($paramNames, $defaultSort)
    {
        $params = [
            'page'    => !empty($this->params()->fromQuery('page')) ? $this->params()->fromQuery('page') : 1,
            'sort'    => !empty($this->params()->fromQuery('sort')) ? $this->params()->fromQuery('sort') : $defaultSort,
            'order'   => !empty($this->params()->fromQuery('order')) ? $this->params()->fromQuery('order') : 'DESC',
            'limit'   => !empty($this->params()->fromQuery('limit')) ? $this->params()->fromQuery('limit') : 10,
        ];

        $params = array_merge($this->params()->fromQuery(), $params);

        foreach ((array) $paramNames as $key => $varName) {
            if (is_int($key)) {
                $params[$varName] = !empty($this->params()->fromRoute($varName)) ?
                    $this->params()->fromRoute($varName) : null;
            } else {
                $params[$key] = !empty($this->params()->fromRoute($varName)) ?
                    $this->params()->fromRoute($varName) : null;
            }
        }

        $params = array_filter($params);

        return $params;
    }

    private function getDefaultFormData($arr)
    {
        $params = [];

        foreach ((array) $arr as $key => $value) {
            if ($value === 'route') {
                $params[$key] = $this->params()->fromRoute($key);
            } else {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    protected function getItemParams($paramNames)
    {
        $params = [];

        foreach ((array) $paramNames as $key => $varName) {
            if (is_int($key)) {
                $params[$varName] = $this->params()->fromRoute($varName);
            } else {
                $params[$key] = $this->params()->fromRoute($varName);
            }
        }

        return $params;
    }

    /**
     * @param array $restResponse
     * @return array
     */
    public function redirectConfig(array $restResponse)
    {
        $action = $this->params()->fromRoute('action', null);
        $action = strtolower($action);

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
                $this->routeIdentifier => null // ID Not required for index.
            ],
            'reUseParams' => true
        ];

        $routeParams = ArrayUtils::merge($defaults, $extraConfig);

        return $this->redirect()->toRouteAjax(
            $routeParams['route'],
            $routeParams['params'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            $routeParams['reUseParams']
        );
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
     * Utility method that returns an instance of the logger.
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->getServiceLocator()->get('Logger');
    }
}
