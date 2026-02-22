<?php

namespace Olcs\Controller;

use Common\Controller\AbstractOlcsController;
use Common\Data\Mapper\DefaultMapper;
use Common\Data\Mapper\MapperInterface;
use Common\Form\Form;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Laminas\Http\PhpEnvironment\Response as PhpEnvironmentResponse;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Config\DataSource\AbstractDataSource;
use Olcs\Controller\Config\DataSource\DataSourceInterface;
use Olcs\Logging\Log\Logger;
use Permits\Data\Mapper\MapperManager;

/**
 * Abstract selfserve controller
 *
 * This is a huge WIP and has been done inside a weekend. Somewhat permits centred for now, but with an eye on reuse
 * So please be nice :)
 */
abstract class AbstractSelfserveController extends AbstractOlcsController
{
    protected const CONDITIONAL_REDIRECT_MSG = 'Key %s from source %s is not equal to %s - redirecting to route %s';

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
        'default' => '',
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

    public function __construct(protected TranslationHelperService $translationHelper, protected FormHelperService $formHelper, protected TableFactory $tableBuilder, protected MapperManager $mapperManager)
    {
    }

    /**
     * onDispatch method
     *
     * @param MvcEvent $e event
     *
     * @return array|mixed
     */
    #[\Override]
    public function onDispatch(MvcEvent $e)
    {
        $params = $this->params();
        $this->routeParams = $params->fromRoute() ?: [];
        $this->postParams = $params->fromPost() ?: [];
        $this->queryParams = $params->fromQuery() ?: [];
        $this->action = strtolower((string) $e->getRouteMatch()->getParam('action'));

        /** @todo find a better place for these */
        $this->retrieveData();

        $response = $this->checkConditionalDisplay();
        if ($response instanceof PhpEnvironmentResponse) {
            return $response;
        }

        $this->mergeTemplateVars();
        $this->retrieveForms();
        $this->retrieveTables();

        return parent::onDispatch($e);
    }

    public function genericView(): ViewModel
    {
        $view = new ViewModel();

        $this->setBrowserTitle();
        $view->setVariable('data', $this->data);
        $view->setVariable('form', $this->form);
        $view->setVariable('forms', $this->forms);
        $view->setVariable('tables', $this->tables);
        $view->setTemplate($this->template);

        return $view;
    }

    /**
     * @return void
     */
    public function mergeTemplateVars()
    {
        $this->template = $this->templateConfig[$this->action] ?? $this->templateConfig['default'];
        $templateVars = $this->configsForAction('templateVarsConfig');
        $this->data = array_merge($this->data, $templateVars);
    }

    public function setBrowserTitle(): void
    {
        if (isset($this->data['browserTitle'])) {
            $prepend = '';
            if ($this->form instanceof Form) {
                if ($this->form->hasValidated()) {
                    if (!$this->formIsValid()) {
                        $prepend = $this->translationHelper->translate('permits.application.browser.title.error') . ': ';
                    }
                }
            }

            $tVarConfig = $this->configsForAction('templateVarsConfig');

            if (array_key_exists('prependTitleDataKey', $tVarConfig) && isset($this->data[$tVarConfig['prependTitleDataKey']])) {
                $prepend .= $this->data[$tVarConfig['prependTitleDataKey']]['prependTitle'] . ' - ';
            }

            $this->placeholder()->setPlaceholder('pageTitle', $prepend . $this->translationHelper->translate($this->data['browserTitle']));
        }
    }

    /**
     * @return HttpResponse|ViewModel
     */
    public function genericAction()
    {
        $response = $this->handlePost();
        if ($response instanceof \Laminas\Http\Response) {
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
     *
     * @return HttpResponse|null
     */
    public function handlePost()
    {
        if (!empty($this->postParams)) {
            $formData = $this->postParams;
            $this->form->setData($formData);

            if ($this->formIsValid()) {
                $config = $this->configsForAction('postConfig');
                // If controller has specified a mapper class, use it instead of default.
                $mapperClass = DefaultMapper::class;
                if (isset($config['mapperClass'])) {
                    $mapperClass = $config['mapperClass'];
                }

                $mapper = $this->mapperManager->get($mapperClass);

                $saveData = [];

                if (isset($formData['fields'])) {
                    $saveData = $mapper->mapFromForm($formData);
                }

                $params = array_merge($this->fetchHandlePostParams(), $saveData);
                $this->redirectParams = [];
                $this->redirectOptions = [];

                $postResponse = $this->handlePostCommand($config, $params);

                if (isset($config['conditional'])) {
                    $dataKey = $config['conditional']['dataKey'];
                    $field = $config['conditional']['field'];
                    $value = $config['conditional']['value'] ?? $params[$config['conditional']['compareParam']];

                    if ($this->data[$dataKey][$field] === $value) {
                        return $this->redirectConditionalPost($config);
                    }
                }

                $saveAndReturnStep = $config['saveAndReturnStep'] ?? '';

                // If mapper implements this method use it to set redirect params.
                if (method_exists($mapper, 'processRedirectParams')) {
                    $this->redirectParams = $mapper->processRedirectParams(
                        $postResponse,
                        $this->routeParams,
                        $formData,
                        $this->data
                    );
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
        $params = $config['defaultParams'] ?? [];

        if (isset($config['params']['route'])) {
            foreach ($config['params']['route'] as $param) {
                if (!empty($this->routeParams[$param])) {
                    $params[$param] = $this->routeParams[$param];
                }
            }
        }

        if (isset($config['params']['query'])) {
            foreach ($config['params']['query'] as $param) {
                if (!empty($this->queryParams[$param])) {
                    $params[$param] = $this->queryParams[$param];
                }
            }
        }

        return $params;
    }

    /**
     * Retrieve data for the specified DTOs
     *
     * @return void
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
             * @var AbstractDataSource $source
             * @var QueryInterface $query
             * @psalm-suppress UndefinedClass
             */
            $source = new $dataSource();
            $query = $source->queryFromParams(array_merge($this->routeParams, $this->queryParams));

            $response = $this->handleQuery($query);
            $data = $this->handleResponse($response);

            if (isset($config['mapper'])) {
                $mapper = $config['mapper'] ?? DefaultMapper::class;
                $data = $this->mapperManager->get($mapper)->mapForDisplay($data);
            }

            $this->data[$source::DATA_KEY] = $data;

            if (isset($config['append'])) {
                foreach ($config['append'] as $appendTo => $mapper) {
                    $combinedData = [
                        $appendTo => $this->data[$appendTo],
                        $source::DATA_KEY => $data
                    ];

                    $this->data[$appendTo] = $this->mapperManager->get($mapper)->mapForDisplay($combinedData);
                }
            }
        }
        $this->data['routeParams'] = $this->routeParams;
    }

    /**
     * Retrieve the configured forms, and set the data
     *
     * @return void
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

            $mapper = $this->mapperManager->get($mapperClass);
            if (isset($config['mapper']['type'])) {
                $methodName = $config['mapper']['type'];
                $this->data = $mapper->$methodName($this->data, $form);
            } elseif (isset($config['dataSource'])) {
                $formData = $mapper->mapFromResult($this->data[$config['dataSource']]);
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
     *
     * @return void
     */
    public function retrieveTables()
    {
        if (!$this->shouldRunOnRequest(__FUNCTION__)) {
            return;
        }

        $tableConfig = $this->configsForAction('tableConfig');

        foreach ($tableConfig as $name => $config) {
            $tableData = $this->data[$config['dataSource']] ?? [];
            $this->tables[$name] = $this->getTable($config['tableName'], $tableData);
        }
    }

    /**
     * @todo only checks that a value matches, needs a wider range of conditions + ability to call custom methods
     * also needs to be made easier to check multiple conditions at once, and fail more elegantly (throw exception?) in
     * cases of bad/missing config
     *
     * @return HttpResponse|null
     */
    public function checkConditionalDisplay()
    {
        if (!$this->shouldRunOnRequest(__FUNCTION__)) {
            return;
        }

        $conditionalDisplayConfig = $this->configsForAction('conditionalDisplayConfig');
        foreach ($conditionalDisplayConfig as $criteria) {
            $data = $this->data[$criteria['source']];

            //if checking a specific key enter the first block, if no key being checked, verify the data isn't empty
            if ($data[$criteria['key']] === $criteria['value']) {
                continue;
            }

            $message = sprintf(
                self::CONDITIONAL_REDIRECT_MSG,
                $criteria['key'],
                $criteria['source'],
                $criteria['value'],
                $criteria['route']
            );

            Logger::info($message);

            return $this->conditionalDisplayNotMet($criteria['route']);
        }
    }

    /**
     * Redirect to the specified route if the conditional display isn't met
     *
     * @param string $route the new route
     *
     * @return \Laminas\Http\Response
     */
    protected function conditionalDisplayNotMet(string $route)
    {
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
     *
     * @SuppressWarnings (PHPMD.UnusedFormalParameter)
     *
     * @SuppressWarnings (PHPMD.UnusedFormalParameter)
     */
    protected function handleResponseErrors(CqrsResponse $response): void
    {
        //handle response errors
    }

    /**
     * @todo make table builder plugin available here - or improve this some other way (needs sort/pagination etc + ZF3)
     */
    protected function getTable(string $tableName, array $data)
    {
        $params = array_merge(
            $this->queryParams,
            ['query' => $this->queryParams]
        );

        return $this->tableBuilder->prepareTable($tableName, $data, $params);
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
        $form = $this->formHelper->createForm($formName, true, false);
        $this->formHelper->setFormActionFromRequest($form, $this->getRequest());

        return $form;
    }

    /**
     * @todo this is somewhat permits specific, but can be generic once permits has switched to use VOL generic buttons
     * currently asks EcmtSection view helper for routes, whereas it would be better to have something specific to route
     * ordering, that will automatically "know" the next route - this will also be needed to make this method generic
     * for wider selfserve use. Permits needs to start using VOL standard buttons before this can be truly reusable
     *
     * @param array  $submittedData     an array of the data submitted by the form
     * @param string $nextStep          the route to be taken if the form was submitted normally
     * @param string $saveAndReturnStep the route to be taken is the form was submitted using save and return
     * @param array  $params            route params
     * @param array  $options           route options
     *
     * @return HttpResponse
     */
    protected function handleSaveAndReturnStep(
        array $submittedData,
        string $nextStep,
        string $saveAndReturnStep,
        array $params = [],
        array $options = []
    ): HttpResponse {
        $step = $nextStep;

        if ($this->isSaveAndReturn($submittedData)) {
            $step = $saveAndReturnStep;
        }

        Logger::debug('Redirecting to route: ' . $step, $this->routeParams);

        return $this->nextStep($step, $params, $options);
    }

    /**
     * Returns true if this is a save and return post, false if not
     * (Checks whether a button other than the primary submit or change button was clicked)
     *
     * @param array $submittedData the post data
     *
     * @return bool
     */
    protected function isSaveAndReturn(array $submittedData): bool
    {
        return !isset($submittedData['Submit']['SubmitButton']) && !isset($submittedData['Submit']['ChangeButton']);
    }

    /**
     * Redirects to the next step in the journey
     *
     * @param string $route   route
     * @param array  $params  route params
     * @param array  $options route options
     *
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
        $data = $this->getCurrentUser();
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

        return $organisation['id'] ?? null;
    }

    /**
     * Allow for different commands and redirects to be triggered based on certain conditions
     *
     * @param array $config conditional config
     *
     * @return HttpResponse
     */
    protected function redirectConditionalPost(array $config)
    {
        /** @note the way this is written, it allows saveAndReturnStep to be omitted from configs if it isn't needed */
        if ($this->isSaveAndReturn($this->postParams)) {
            $condition = $config['conditional']['saveAndReturnStep'];
            $step = '';
            $saveAndReturnStep = $condition['route'];
        } else {
            $condition = $config['conditional']['step'];
            $step = $condition['route'];
            $saveAndReturnStep = '';
        }

        if (isset($condition['command'])) {
            /** @var CommandInterface $conditionalCommand */
            $conditionalCommand = $condition['command']::create([
                $config['conditional']['params'] =>
                    $this->data[$config['conditional']['dataKey']][$config['conditional']['params']]
            ]);

            Logger::debug(
                'Sending conditional command: ' . $conditionalCommand::class,
                $conditionalCommand->getArrayCopy()
            );

            $conditionalResponse = $this->handleCommand($conditionalCommand);
            $this->handleResponse($conditionalResponse);
        }

        $conditionalQueryParams = $config['conditional']['query'] ?? [];

        return $this->handleSaveAndReturnStep(
            $this->postParams,
            $step,
            $saveAndReturnStep,
            ['id' => $this->data[$config['conditional']['dataKey']]['id']],
            ['query' => $conditionalQueryParams]
        );
    }

    /**
     * Dispatch POST command
     *
     *
     * @return array|null
     */
    protected function handlePostCommand(array &$config, array $params)
    {
        if (isset($config['command'])) {
            $command = $config['command']::create($params);
            $response = $this->handleCommand($command);

            return $this->handleResponse($response);
        }
    }

    /**
     * Whether the form is valid
     *
     * @return bool
     */
    protected function formIsValid()
    {
        return $this->form->isValid();
    }
}
