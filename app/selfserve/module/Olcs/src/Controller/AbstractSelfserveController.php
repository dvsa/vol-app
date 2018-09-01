<?php

namespace Olcs\Controller;

use Common\Controller\AbstractOlcsController;
use Common\Data\Mapper\DefaultMapper;
use Common\Data\Mapper\MapperInterface;
use Common\Form\Form;
use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

/**
 * Abstract selfserve controller
 *
 * This is a huge WIP and has been done inside a weekend. Somewhat permits centred for now, but with an eye on reuse
 * So please be nice :)
 */
class AbstractSelfserveController extends AbstractOlcsController
{
    /**
     * The current controller action
     *
     * @var string
     */
    protected $action;

    /**
     * Route params to retrieve
     *
     * @var array
     */
    protected $paramsConfig = ['id'];

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
     * Tables which have been created and are ready to pass to the view
     *
     * @var array
     */
    protected $params = [];

    /**
     * onDispatch method
     *
     * @param MvcEvent $e event
     *
     * @return array|mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        foreach ($this->paramsConfig as $param) {
            $this->params = $this->params()->fromRoute($param);
        }

        $this->action = strtolower($e->getRouteMatch()->getParam('action'));
        $this->retrieveData();
        $this->checkConditionalDisplay();
        $this->retrieveForms();
        $this->retrieveTables();

        return parent::onDispatch($e);
    }

    public function genericView()
    {
        $view = new ViewModel();

        $view->setVariable('data', $this->data);
        $view->setVariable('forms', $this->forms);
        $view->setVariable('tables', $this->tables);

        return $view;
    }

    public function genericAction()
    {
        $view = $this->genericView();
        $view->setTemplate('permits/check-answers');

        return $view;
    }


    /**
     * Retrieve data for the specified DTOs
     */
    public function retrieveData()
    {
        $dataSourceConfig = $this->configsForAction('dataSourceConfig');

        //retrieve DTO data
        foreach ($dataSourceConfig as $key => $config) {
            $queryData = [];

            foreach ($config['params'] as $param) {
                $queryData[$param] = $this->params[$param];
            }

            /** @var QueryInterface $query */
            $query = $config['dto']::create($queryData);
            $response = $this->handleQuery($query);
            $this->data[$key] = $this->handleResponse($response);
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
     * @todo only checks that a value is true, needs a wider range of conditions + ability to call custom methods
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
}
