<?php

namespace Olcs\Controller;

use Common\Controller\AbstractOlcsController;
use Common\Data\Mapper\DefaultMapper;
use Common\Data\Mapper\MapperInterface;
use Common\Form\Form;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Olcs\Controller\Config\DataSource\DataSourceInterface;
use Permits\View\Helper\EcmtSection;
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
        'cancel' => ''
    ];

    /**
     * @todo look at where this could be made generic
     *
     * @var array
     */
    protected $postConfig = [];

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
        $this->retrieveForms();
        $this->retrieveTables();

        return parent::onDispatch($e);
    }

    public function genericView()
    {
        $view = new ViewModel();

        /** @todo map the data for display */
        $this->mapDataForDisplay();

        $view->setVariable('data', $this->data);
        $view->setVariable('form', $this->form);
        $view->setVariable('forms', $this->forms);
        $view->setVariable('tables', $this->tables);
        $view->setTemplate($this->templateConfig[$this->action]);

        return $view;
    }

    public function mapDataForDisplay()
    {
        //
    }

    public function genericAction()
    {
        $this->handlePost();
        return $this->genericView();
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
     * @todo add in mapping data back from the form, right now this only works for confirmation pages
     * @todo handle redirects, currently just assumes a "next step" is present
     * @todo need to put in some error handling to help devs diagnose bad config etc.
     */
    public function handlePost()
    {
        if (!empty($this->postParams)) {
            $this->form->setData($this->postParams);

            if ($this->form->isValid()) {
                $config = $this->configsForAction('postConfig');
                $params = $this->fetchHandlePostParams();
                $command = $config['command']::create($params);
                $response = $this->handleCommand($command);
                $this->handleResponse($response);

                return $this->handleSaveAndReturnStep($this->postParams, $config['step']);
            }
        }
    }

    /**
     * @todo error handling to help spot bad config, probably split into route/query etc
     */
    public function fetchHandlePostParams()
    {
        $params = [];
        $config = $this->configsForAction('postConfig');

        if (isset($config['params']['route'])) {
            foreach($config['params']['route'] as $param) {
                $params[$param] = $this->routeParams[$param];
            }
        }

        if (isset($config['params']['query'])) {
            foreach($config['params']['query'] as $param) {
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
        $dataSourceConfig = $this->configsForAction('dataSourceConfig');

        //retrieve DTO data
        foreach ($dataSourceConfig as $dataSource => $config) {
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
                $data = $mapper::mapForDisplay($data);
            }

            $this->data[$source::DATA_KEY] = $data;
        }
    }

    /**
     * Retrieve the configured forms, and set the data
     */
    public function retrieveForms()
    {
        $formConfig = $this->configsForAction('formConfig');

        foreach ($formConfig as $name => $config) {
            $formData = [];

            if (isset($config['dataSource'])) {
                /** @var MapperInterface $mapperClass */
                $mapperClass = isset($config['mapper']) ? $config['mapper'] : DefaultMapper::class;
                $formData = $mapperClass::mapFromResult($this->data[$config['dataSource']]);
            }

            $form = $this->getForm($config['formClass']);

            $form->setData($formData);
            $this->forms[$name] = $form;

            /**
             * @todo everything we do just has one form, this is a quick way during dev to avoid changing existing code
             */
            $this->form = $form;
        }
    }

    /**
     * Retrieve the configured tables
     */
    public function retrieveTables()
    {
        $tableConfig = $this->configsForAction('tableConfig');

        foreach ($tableConfig as $name => $config) {
            $tableData = $this->data[$config['dataSource']];
            $this->tables[$name] = $this->getTable($config['tableName'], $tableData);
        }
    }

    /**
     * @todo only checks that a value matches, needs a wider range of conditions + ability to call custom methods
     * also needs to be made easier to check multiple conditions at once, and fail more elegantly (throw exception?) in
     * cases of bad/missing config
     */
    public function checkConditionalDisplay()
    {
        $conditionalDisplayConfig = $this->configsForAction('conditionalDisplayConfig');

        foreach ($conditionalDisplayConfig as $source => $criteria) {
            $data = $this->data[$source];

            if ($data[$criteria['key']] === $criteria['value']) {
                continue;
            }

            return $this->conditionalDisplayNotMet();
        }
    }

    /**
     * @todo needs to be more configurable (right now would need to be overridden), possibly add flash message
     *
     * @return \Zend\Http\Response
     */
    protected function conditionalDisplayNotMet()
    {
        return $this->redirect()->toRoute('permits');
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
            ->prepareTable($tableName, $data['results']);
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
     * @param $submittedData - an array of the data submitted by the form
     * @param $nextStep - the EcmtSection:: route to be taken if the form was submitted normally
     *
     * @return HttpResponse
     */
    protected function handleSaveAndReturnStep(array $submittedData, string $nextStep): HttpResponse
    {
        if (array_key_exists('SubmitButton', $submittedData['Submit'])) {
            //Form was submitted normally so continue on chosen path
            return $this->nextStep($nextStep);
        }

        //A button other than the primary submit button was clicked so return to overview
        return $this->nextStep(EcmtSection::ROUTE_APPLICATION_OVERVIEW);
    }

    /**
     * @todo same as handleSaveAndReturnStep, in that this is currently permits specific, but can easily be made generic
     *
     * @param string $route
     *
     * @return HttpResponse
     */
    protected function nextStep(string $route): HttpResponse
    {
        return $this->redirect()->toRoute('permits/' . $route, [], [], true);
    }
}
