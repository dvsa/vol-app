<?php

namespace Olcs\Controller;

use Common\Controller\AbstractOlcsController;
use Common\Data\Mapper\DefaultMapper;
use Common\Data\Mapper\MapperInterface;
use Common\Form\Form;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Olcs\Controller\Config\DataSource\DataSourceInterface;
use Permits\View\Helper\EcmtSection;
use ReflectionMethod;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

/**
 * Abstract selfserve controller
 *
 * This is a huge WIP and has been done inside a weekend. Somewhat permits centred for now, but with an eye on reuse
 * So please be nice :)
 */
abstract class AbstractSelfserveController extends AbstractOlcsController
{
    /**
     * The current controller action
     *
     * @var string
     */
    protected $action;

    /**
     * Config to pull back various sources of data
     *
     * @var array
     */
    protected $dataSourceConfig = [];

    /**
     * Config to create forms
     *
     * @var array
     */
    protected $formConfig = [];

    /**
     * Config for table retrieval
     *
     * @var array
     */
    protected $tableConfig = [];

    /**
     * Config for template variables
     *
     * @var array
     */
    protected $templateVarsConfig = [];

    /**
     * Manage conditional display of actions i.e. should the user be allowed to reach this point
     *
     * @var array
     */
    protected $conditionalDisplayConfig = [];

    /**
     * Data which has been retrieved and is ready to map to forms, or pass to view
     *
     * @var array
     */
    protected $data = [];

    /**
     * Form ready to be manipulated or passed to the view
     *
     * @var Form
     */
    protected $form;

    /**
     * Forms which have been created and are ready to be manipulated or passed to the view
     *
     * @var array
     */
    protected $forms = [];

    /**
     * Tables which have been created and are ready to pass to the view
     *
     * @var array
     */
    protected $tables = [];

    /**
     * Template name
     *
     * @var string
     */
    protected $template = '';

    /**
     * Route parameters
     *
     * @var array
     */
    protected $routeParams = [];

    /**
     * Post parameters
     *
     * @var array
     */
    protected $postParams = [];

    /**
     * Query parameters
     *
     * @var array
     */
    protected $queryParams = [];

    /**
     * @todo add default reusable templates (probably just output a form etc.) for now extending classes must override
     *
     * @var array
     */
    protected $templateConfig = [
        'generic' => '',
        'question' => 'permits/single-question',
        'cancel' => '',
    ];

    /**
     * @todo look at where this could be made generic
     *
     * @var array
     */
    protected $postConfig = [];

    /**
     * Redirect parameters
     *
     * @var array
     */
    protected $redirectParams;

    /**
     * Redirect options
     *
     * @var array
     */
    protected $redirectOptions;

    /**
     * onDispatch method
     *
     * @param MvcEvent $e event
     *
     * @return array|mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        $params = $this->params();
        $this->routeParams = $params->fromRoute() ? $params->fromRoute() : [];
        $this->postParams = $params->fromPost() ? $params->fromPost() : [];
        $this->queryParams = $params->fromQuery() ? $params->fromQuery() : [];
        $this->action = strtolower($e->getRouteMatch()->getParam('action'));

        /** @todo find a better place for these */
        $this->retrieveData();
        $this->checkConditionalDisplay();
        $this->mergeTemplateVars();
        $this->retrieveForms();
        $this->retrieveTables();

        return parent::onDispatch($e);
    }

    public function genericView()
    {
        $view = new ViewModel();

        $view->setVariable('data', $this->data);
        $view->setVariable('form', $this->form);
        $view->setVariable('forms', $this->forms);
        $view->setVariable('tables', $this->tables);
        $view->setTemplate($this->template);

        return $view;
    }

    public function mergeTemplateVars()
    {
        $this->template = isset($this->templateConfig[$this->action]) ? $this->templateConfig[$this->action] : $this->templateConfig['default'];
        $templateVars = $this->configsForAction('templateVarsConfig');
        $this->data = array_merge($this->data, $templateVars);

        if (isset($templateVars['browserTitle'])) {
            $headTitle = $this->getServiceLocator()->get('ViewHelperManager')->get('headTitle');
            $headTitle->setSeparator(' - ');
            $headTitle->prepend($templateVars['browserTitle']);
        }
    }

    public function genericAction()
    {
        $response = $this->handlePost();
        if ($response instanceof \Zend\Http\Response) {
            return $response;
        }

        return $this->genericView();
    }

    public function questionAction()
    {
        return $this->genericAction();
    }

    public function cancelAction()
    {
        return $this->genericAction();
    }

    public function withdrawAction()
    {
        return $this->genericAction();
    }

    public function confirmationAction()
    {
        return $this->genericAction();
    }

    /**
     * @todo handle redirects, currently just assumes a "next step" is present
     * @todo need to put in some error handling to help devs diagnose bad config etc.
     */
    public function handlePost()
    {
        if (!empty($this->postParams)) {
            $config = $this->configsForAction('postConfig');
            // If controller has specified a mapper class, use it instead of default.
            $mapper = DefaultMapper::class;
            if (isset($config['mapperClass'])) {
                $mapper = $config['mapperClass'];
            }

            $formData = $this->postParams;
            // If controller specified a pre-process mapper method, invoke it before setting data to form.
            if (isset($config['preprocessMethod']) && method_exists($mapper, $config['preprocessMethod'])) {
                $preProcess = $mapper::{$config['preprocessMethod']}($this->postParams, $this->form);
                $formData = $preProcess['formData'];
            }

            $this->form->setData($formData);
            if ($this->form->isValid() && !isset($preProcess['invalidForm'])) {
                $saveData = [];

                if (isset($formData['fields'])) {
                    $saveData = $mapper::mapFromForm($formData);
                }

                $params = array_merge($this->fetchHandlePostParams(), $saveData);
                $this->redirectParams = [];
                $this->redirectOptions = [];

                $this->handlePostCommand($config, $params);

                if (isset($config['conditional'])) {
                    $dataKey = $config['conditional']['dataKey'];
                    $field = $config['conditional']['field'];
                    $value = $config['conditional']['value'];

                    if (is_array($field) && array_search($params[$value], $this->data[$dataKey][$field[0]]) === $field[1]
                        || !is_array($field) && $this->data[$dataKey][$field] === $value) {
                        return $this->redirectConditionalPost($config);
                    }
                }

                if (isset($config['saveAndReturnStep'])) {
                    $saveAndReturnStep = $config['saveAndReturnStep'];
                } else {
                    $saveAndReturnStep = EcmtSection::ROUTE_APPLICATION_OVERVIEW;
                }

                return $this->handleSaveAndReturnStep(
                    $this->postParams,
                    $config['step'],
                    $saveAndReturnStep,
                    $this->redirectParams,
                    $this->redirectOptions
                );
            }
        }
    }

    /**
     * @todo error handling to help spot bad config, probably split into route/query etc
     */
    public function fetchHandlePostParams()
    {
        $config = $this->configsForAction('postConfig');
        $params = isset($config['defaultParams']) ? $config['defaultParams'] : [];

        if (isset($config['params']['route'])) {
            foreach ($config['params']['route'] as $param) {
                $params[$param] = $this->routeParams[$param];
            }
        }

        if (isset($config['params']['query'])) {
            foreach ($config['params']['query'] as $param) {
                $params[$param] = $this->queryParams[$param];
            }
        }

        return $params;
    }

    /**
     * Retrieve data for the specified DTOs
     */
    public function retrieveData()
    {
        if (!$this->shouldRunOnRequest(__FUNCTION__)) {
            return;
        }

        $dataSourceConfig = $this->configsForAction('dataSourceConfig');

        //retrieve DTO data
        foreach ($dataSourceConfig as $dataSource => $config) {
            // If we need to pass in data (not from the route)
            if (isset($config['passInData']['key'])) {
                if (isset($config['passInData']['func'])) {
                    $this->queryParams[$config['passInData']['key']] = call_user_func_array([$this, $config['passInData']['func']], []);
                } elseif (isset($config['passInData']['value'])) {
                    $this->queryParams[$config['passInData']['key']] = $config['passInData']['value'];
                }
            }

            /**
             * @var DataSourceInterface $source
             * @var QueryInterface $query
             */
            $source = new $dataSource();
            $query = $source->queryFromParams(array_merge($this->routeParams, $this->queryParams));

            $response = $this->handleQuery($query);
            $data = $this->handleResponse($response);

            if (isset($config['mapper'])) {
                $mapper = isset($config['mapper']) ? $config['mapper'] : DefaultMapper::class;
                $reflection = new ReflectionMethod($mapper, 'mapForDisplay');

                if ($reflection->getNumberOfRequiredParameters() > 2) {
                    $data = $mapper::mapForDisplay($data, $this->getServiceLocator()->get('Helper\Translation'), $this->url());
                } else {
                    $data = $mapper::mapForDisplay($data);
                }
            }

            $this->data[$source::DATA_KEY] = $data;

            if (isset($config['append'])) {
                foreach ($config['append'] as $appendTo => $mapper) {
                    $combinedData = [
                        $appendTo => $this->data[$appendTo],
                        $source::DATA_KEY => $data
                    ];

                    $reflection = new ReflectionMethod($mapper, 'mapForDisplay');

                    if ($reflection->getNumberOfRequiredParameters() > 2) {
                        $this->data[$appendTo] = $mapper::mapForDisplay($combinedData, $this->getServiceLocator()->get('Helper\Translation'), $this->url());
                    } else {
                        $this->data[$appendTo] = $mapper::mapForDisplay($combinedData);
                    }
                }
            }
        }
    }

    /**
     * Retrieve the configured forms, and set the data
     */
    public function retrieveForms()
    {
        if (!$this->shouldRunOnRequest(__FUNCTION__)) {
            return;
        }

        $formConfig = $this->configsForAction('formConfig');

        foreach ($formConfig as $name => $config) {
            $formData = [];
            $form = $this->getForm($config['formClass']);
            /** @var MapperInterface $mapperClass */
            $mapperClass = isset($config['mapper']) ? $config['mapper']['class'] : DefaultMapper::class;

            if (isset($config['dataParam'])) {
                $this->data[$config['dataParam']] = $this->params()->fromQuery($config['dataParam']);
            }

            if (isset($config['dataRouteParam'])) {
                $this->data[$config['dataRouteParam']] = $this->params()->fromRoute($config['dataRouteParam']);
            }

            if (isset($config['mapper']['type'])) {
                $this->data = call_user_func_array([$mapperClass, $config['mapper']['type']], [$this->data, $form, $this->getServiceLocator()->get('Helper\Translation')]);
            } elseif (isset($config['dataSource'])) {
                $formData = $mapperClass::mapFromResult($this->data[$config['dataSource']]);
            }

            $form->setData($formData);
            $form = $this->alterForm($form);
            $this->forms[$name] = $form;

            /**
             * @todo everything we do just has one form, this is a quick way during dev to avoid changing existing code
             */
            $this->form = $form;
        }
    }

    /**
     * Can be overidden in controller to manipulate
     *
     * @param \Common\Form\Form $form
     * @return \Common\Form\Form
     */
    public function alterForm($form)
    {
        return $form;
    }

    /**
     * Retrieve the configured tables
     */
    public function retrieveTables()
    {
        if (!$this->shouldRunOnRequest(__FUNCTION__)) {
            return;
        }

        $tableConfig = $this->configsForAction('tableConfig');

        foreach ($tableConfig as $name => $config) {
            $tableData = isset($this->data[$config['dataSource']]) ? $this->data[$config['dataSource']] : [];
            $this->tables[$name] = $this->getTable($config['tableName'], $tableData, $this->queryParams);
        }
    }

    /**
     * @todo only checks that a value matches, needs a wider range of conditions + ability to call custom methods
     * also needs to be made easier to check multiple conditions at once, and fail more elegantly (throw exception?) in
     * cases of bad/missing config
     */
    public function checkConditionalDisplay()
    {
        if (!$this->shouldRunOnRequest(__FUNCTION__)) {
            return;
        }

        $conditionalDisplayConfig = $this->configsForAction('conditionalDisplayConfig');
        foreach ($conditionalDisplayConfig as $source => $criteria) {
            $data = $this->data[$source];

            // Validate result if a key/value condition is defined or if a key is defined
            if (isset($criteria['key']) && isset($criteria['value']) && $data[$criteria['key']] === $criteria['value']) {
                continue;
            } elseif (isset($criteria['view']) && !empty($data[$source]) && !isset($criteria['key'])) {
                continue;
            }

            $route = isset($criteria['route']) ? $criteria['route'] : null;
            $view = isset($criteria['view']) ? $criteria['view'] : null;
            return $this->conditionalDisplayNotMet($view, $route);
        }
    }

    /**
     *
     * @return \Zend\Http\Response
     */
    protected function conditionalDisplayNotMet($view = null, $route = null)
    {
        if (!is_null($view)) {
            $this->templateConfig[$this->action] = $view['template'];

            if (isset($view['data'])) {
                array_merge($this->templateVarsConfig[$this->action], $view['data']);
            }

            return $this->genericView();
        }

        $route = $route ? $route : 'permits';
        return $this->redirect()->toRoute($route, [], [], true);
    }

    /**
     * Return the config (table/form etc.) based on the controller action
     *
     * @param string $config config key
     *
     * @return array
     */
    protected function configsForAction(string $config): array
    {
        $configs = [];
        $configKey = $this->$config;
        $action = $this->action;

        if (isset($configKey[$action])) {
            $configs = $configKey[$action];
        } elseif (isset($configKey['default'])) {
            $configs = $configKey['default'];
        }

        return $configs;
    }

    /**
     * Handle the API response
     *
     * @param CqrsResponse $response
     *
     * @return array
     */
    protected function handleResponse(CqrsResponse $response)
    {
        if (!$response->isOk()) {
            $this->handleResponseErrors($response);
        }

        return $response->getResult();
    }

    /**
     * @todo needs to handle response errors :)
     *
     * @param CqrsResponse $response
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function handleResponseErrors(CqrsResponse $response)
    {
        //handle response errors
    }

    /**
     * @todo make table builder plugin available here - or improve this some other way (needs sort/pagination etc + ZF3)
     */
    protected function getTable(string $tableName, array $data)
    {
        return $this->getServiceLocator()
            ->get('Table')
            ->prepareTable($tableName, $data, $this->queryParams);
    }

    /**
     * @todo carried over from elsewhere and is a bit rubbish, at very least it would be nice to have ::class + ZF3
     *
     * @param string $formName name of the form
     *
     * @return Form
     */
    protected function getForm(string $formName): Form
    {
        /** @var FormHelperService $formHelper */
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm($formName, true, false);
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        return $form;
    }

    /**
     * @todo this is somewhat permits specific, but can be generic once permits has switched to use VOL generic buttons
     * currently asks EcmtSection view helper for routes, whereas it would be better to have something specific to route
     * ordering, that will automatically "know" the next route - this will also be needed to make this method generic
     * for wider selfserve use. Permits needs to start using VOL standard buttons before this can be truly reusable
     *
     * @param array $submittedData - an array of the data submitted by the form
     * @param string $nextStep - the route to be taken if the form was submitted normally
     * @param string $saveAndReturnStep - the route to be taken is the form was submitted using save and return
     * @param array $params
     * @param array $options
     * @return HttpResponse
     */
    protected function handleSaveAndReturnStep(
        array $submittedData,
        string $nextStep,
        $saveAndReturnStep = EcmtSection::ROUTE_APPLICATION_OVERVIEW,
        array $params = [],
        array $options = []
    ): HttpResponse {
        if (array_key_exists('SubmitButton', $submittedData['Submit']) || array_key_exists('ChangeButton', $submittedData['Submit'])) {
            // Form was submitted normally so continue on chosen path
            $step = $nextStep;
        } else {
            // A button other than the primary submit button was clicked so return to overview
            $step = $saveAndReturnStep;
        }

        return $this->nextStep($step, $params, $options);
    }

    /**
     * @todo same as handleSaveAndReturnStep, in that this is currently permits specific, but can easily be made generic
     *
     * @param string $route
     *
     * @param array $params
     * @param array $options
     * @return HttpResponse
     */
    protected function nextStep(string $route, array $params = [], array $options = []): HttpResponse
    {
        return $this->redirect()->toRoute($route, $params, $options, true);
    }

    /**
     * Decide whether a method needs to run for this request. Right now this is only implemented for POST requests
     * and neither does it support anything conditional. This can be added later.
     *
     * @param string $method the method we wish to run
     *
     * @return bool
     */
    protected function shouldRunOnRequest(string $method): bool
    {
        if ($this->request->isPost()) {
            return $this->shouldRunOnPost($method);
        }

        return $this->shouldRunOnGet($method);
    }

    /**
     * Returns whether a method should run for POST requests
     *
     * @param string $method the method we wish to run
     *
     * @return bool
     */
    protected function shouldRunOnPost(string $method): bool
    {
        $config = $this->configsForAction('postConfig');

        if (isset($config[$method]) && !$config[$method]) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether a method should run for GET requests - for now everything will
     *
     * @param string $method the method we wish to run
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldRunOnGet(string $method): bool
    {
        return true;
    }

    /**
     * Define is Nothern Ireland
     *
     * @param array $params Lva object data
     *
     * @return bool
     */
    protected function isNi(array $params)
    {
        if (isset($params['niFlag'])) {
            return ValueHelper::isOn($params['niFlag']);
        }

        if (isset($params['trafficArea']['isNi'])) {
            return (bool)$params['trafficArea']['isNi'];
        }

        if (isset($params['licence']['trafficArea']['isNi'])) {
            return (bool)$params['licence']['trafficArea']['isNi'];
        }

        return false;
    }

    /**
     * Get current user
     *
     * @return array
     */
    protected function getCurrentUser()
    {
        // get user data from Controller Plugin
        return $this->currentUser()->getUserData();
    }

    /**
     * Get current organisation
     *
     * @NOTE at the moment this will just return the users first organisation,
     * eventually the user will be able to select which organisation they are managing
     *
     * @return array
     */
    protected function getCurrentOrganisation()
    {
        $dto = MyAccount::create([]);

        $response = $this->handleQuery($dto);

        if (!$response->isOk()) {
            return null;
        }

        $data = $response->getResult();

        return $data['organisationUsers'][0]['organisation'];
    }

    /**
     * Get current organisation ID only
     *
     * @return int|null
     */
    protected function getCurrentOrganisationId()
    {
        $organisation = $this->getCurrentOrganisation();

        return (isset($organisation['id'])) ? $organisation['id'] : null;
    }

    /**
     * Redirect by conditional
     *
     * @param array $config
     * @return HttpResponse
     */
    protected function redirectConditionalPost(array $config)
    {
        if (isset($config['conditional']['command'])) {
            $conditionalCommand = $config['conditional']['command']::create([
                $config['conditional']['params'] => $this->data[$config['conditional']['dataKey']][$config['conditional']['params']]
            ]);
            $conditionalResponse = $this->handleCommand($conditionalCommand);
            $this->handleResponse($conditionalResponse);
        }

        $conditionalQueryParams = isset($config['conditional']['query']) ? $config['conditional']['query'] : [];

        return $this->redirect()
            ->toRoute(
                $config['conditional']['step'],
                ['id' => $this->data[$config['conditional']['dataKey']]['id']],
                ['query' => $conditionalQueryParams]
            );
    }

    /**
     * Dispatch POST command
     *
     * @param array $config
     * @param array $params
     */
    protected function handlePostCommand(array &$config, array $params)
    {
        if (isset($config['command'])) {
            $command = $config['command']::create($params);
            $response = $this->handleCommand($command);

            $this->handleResponse($response);
        }
    }
}
