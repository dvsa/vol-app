<?php
/**
 * Crud Abstract Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller as CommonController;
use Olcs\Controller\Traits;
use Zend\Mvc\MvcEvent as MvcEvent;

/**
 * Crud Abstract Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
abstract class CrudAbstract extends CommonController\AbstractSectionController implements CommonController\CrudInterface
{
    use Traits\DeleteActionTrait;

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = '';

    protected $requiredProperties = [
        'formName',
        'identifierName',
        'tableName',
        'dataMap',
        'dataBundle',
        'service',
        'pageLayout',
        'listVars'
    ];

    protected $pageLayout = null;

    protected $pageLayoutInner = null;

    protected $detailsView = null;

    protected $defaultTableSortField = 'id';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [];

    /**
     * Holds the isAction
     *
     * @var boolean
     */
    protected $isAction = false;

    /**
     * Contains the name of the view placeholder for the table.
     *
     * @var string
     */
    protected $tableViewPlaceholderName = 'table';

    /**
     * Identifier key
     *
     * @var string
     */
    protected $identifierKey = 'id';

    /**
     * Is the result a result of REST call to getList. Set to true when
     * identifierKey is not 'id'
     *
     * @var bool
     */
    protected $isListResult = false;

    /**
     * Placeholdername
     *
     * @var null
     */
    protected $placeholderName = null;

    /**
     * Listdata
     *
     * @var null
     */
    protected $listData = null;

    /**
     * dataServiceName used to identify the name of the data service. Used for close buttons
     * @var null
     */
    protected $dataServiceName = null;

    /**
     * dataService object. Used for close buttons
     * @var null
     */
    protected $dataService = null;

    protected $entityDisplayName = null;

    protected $isSaved = false;

    /**
     * Get Entity name
     * @return string
     */
    public function getEntityDisplayName()
    {
        return $this->entityDisplayName;
    }

    /**
     * Set entityName
     * @param string $entityDisplayName
     * @return $this
     */
    public function setEntityDisplayName($entityDisplayName)
    {
        $this->entityDisplayName = $entityDisplayName;
        return $this;
    }

    /**
     * Get DataService, look up if not set
     * @return object
     */
    public function getDataService()
    {
        if (isset($this->dataService)) {
            return $this->dataService;
        }
        $dataService = $this->getServiceLocator()->get('Olcs\Service\Data\\' . $this->getService());
        $this->setDataService($dataService);
        return $this->dataService;
    }

    /**
     * @param null $dataServiceName
     *
     * @return $this
     */
    public function setDataServiceName($dataServiceName)
    {
        $this->dataServiceName = $dataServiceName;
        return $this;
    }

    /**
     * @return null
     */
    public function getDataServiceName()
    {
        return $this->dataServiceName;
    }

    /**
     * Set dataService
     * @param $dataService
     */
    public function setDataService($dataService)
    {
        $this->dataService = $dataService;
    }

    /**
     *
     * @param string $placeholderName
     * @return $this
     */
    public function setPlaceholderName($placeholderName)
    {
        $this->placeholderName = $placeholderName;
        return $this;
    }

    /**
     * @return null
     */
    public function getPlaceholderName()
    {
        if (is_null($this->placeholderName)) {
            return $this->getIdentifierName();
        }
        return $this->placeholderName;
    }

    /**
     * @param string $pageLayout
     * @return $this
     */
    public function setPageLayout($pageLayout)
    {
        $this->pageLayout = $pageLayout;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageLayout()
    {
        return $this->pageLayout;
    }

    /**
     * @param string $pageLayoutInner
     * @return $this
     */
    public function setPageLayoutInner($pageLayoutInner)
    {
        $this->pageLayoutInner = $pageLayoutInner;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageLayoutInner()
    {
        return $this->pageLayoutInner;
    }

    /**
     * @param string $detailsView
     * @return $this
     */
    public function setDetailsView($detailsView)
    {
        $this->detailsView = $detailsView;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetailsView()
    {
        return $this->detailsView;
    }

    /**
     * Sets the listData property.
     *
     * @param $listData
     * @return array
     */
    public function setListData($listData)
    {
        $this->listData = $listData;
        return $this;
    }

    /**
     * Returns the listData property.
     *
     * @return array
     */
    public function getListData()
    {
        return $this->listData;
    }

    /**
     * @param boolean $isListResult
     */
    public function setIsListResult($isListResult)
    {
        $this->isListResult = (bool) $isListResult;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isListResult()
    {
        return (bool) $this->isListResult;
    }

    /**
     * Sets the identifier key to use when loading current data bundle. Allows searching by other fields.
     * Defaults to 'id'
     *
     * @param string $identifierKey
     */
    public function setIdentifierKey($identifierKey)
    {
        $this->identifierKey = $identifierKey;
        return $this;
    }

    /**
     * Identifier key to use when loading current data bundle. Allows searching by other fields.
     * Defaults to 'id'
     *
     * @return string
     */
    public function getIdentifierKey()
    {
        return $this->identifierKey;
    }

    /**
     * Stores whether a record has been saved
     *
     * @return bool
     */
    public function getIsSaved()
    {
        return $this->isSaved;
    }

    /**
     * Sets the isSaved variable
     *
     * @param string $isSaved
     * @return $this
     */
    public function setIsSaved($isSaved)
    {
        $this->isSaved = $isSaved;
        return $this;
    }

    /**
     * Sets the form name used by the class
     *
     * @param $formName
     * @return $this
     */
    public function setFormName($formName)
    {
        $this->formName = $formName;
        return $this;
    }

    /**
     * @codeCoverageIgnore this is part of the event system.
     */
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'checkRequiredProperties'), 5);

        if (method_exists($this, 'setNavigationCurrentLocation')) {
            $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'setNavigationCurrentLocation'), 6);
        }
    }

    /**
     * Proxies to the get query or get param.
     *
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
    public function getQueryOrRouteParam($name, $default = null)
    {
        if ($queryValue = $this->params()->fromQuery($name, $default)) {
            return $queryValue;
        }

        if ($queryValue = $this->params()->fromRoute($name, $default)) {
            return $queryValue;
        }

        return $default;
    }

    /**
     * Index Action.
     */
    public function indexAction()
    {
        $view = $this->getView([]);

        $this->checkForCrudAction(null, [], $this->getIdentifierName());

        $this->buildTableIntoView();

        $this->buildCommentsBoxIntoView();

        $view->setTemplate('pages/table-comments');

        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    /**
     * Returns the listVars property.
     *
     * @return array
     */
    public function getListVars()
    {
        return $this->listVars;
    }

    /**
     * Builds table into its placeholder.
     *
     * @return void
     */
    public function buildTableIntoView()
    {
        if ($tableName = $this->getTableName()) {

            $params = $this->getTableParams();

            $data = $this->loadListData($params);

            $data = $this->preProcessTableData($data);

            $this->getViewHelperManager()->get('placeholder')->getContainer($this->getTableViewPlaceholderName())->set(
                $this->alterTable($this->getTable($tableName, $data, $params))
            );
        }
    }

    /**
     * Returns the value of $this->tableViewPlaceholderName
     *
     * @return string
     */
    public function getTableViewPlaceholderName()
    {
        return $this->tableViewPlaceholderName;
    }

    public function loadListData(array $params)
    {
        $listData = $this->getListData();

        if ($listData == null) {
            $this->setListData($this->makeRestCall($this->getService(), 'GET', $params, $this->getDataBundle()));
            $listData = $this->getListData();
        }

        return $listData;
    }

    public function getTableParams()
    {
        $params = [
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', $this->defaultTableSortField),
            'order'   => $this->getQueryOrRouteParam('order', 'DESC'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
        ];

        $listVars = $this->getListVars();
        for ($i=0; $i<count($listVars); $i++) {
            $params[$listVars[$i]] = $this->getQueryOrRouteParam($listVars[$i], null);
        }

        $params['query'] = $this->getRequest()->getQuery();

        return $params;
    }

    /**
     * Pre processes the data before it's injected into the table.
     *
     * @param array $data
     * @return array
     */
    public function preProcessTableData($data)
    {
        return $data;
    }

    /**
     * Master details option.
     */
    public function detailsAction()
    {
        $view = $this->getView([]);

        $result = $this->loadCurrent();

        if (isset($result['id']) &&
            in_array('Olcs\Controller\Traits\CloseActionTrait', class_uses($this))) {
            $view->setVariable('closeAction', $this->generateCloseActionButtonArray($result['id']));
        }

        $this->getViewHelperManager()
             ->get('placeholder')
             ->getContainer($this->getPlaceholderName())
             ->set($result);

        $this->getViewHelperManager()
             ->get('placeholder')
             ->getContainer('details')
             ->set($result);

        $view->setTemplate($this->getDetailsView());
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    /**
     * Sets the view helper placeholder namespaced value.
     *
     * @param string $namespace
     * @param mixed $content
     */
    public function setPlaceholder($namespace, $content)
    {
        $this->getViewHelperManager()->get('placeholder')
             ->getContainer($namespace)->set($content);
    }

    /**
     * Proxy method for save.
     *
     * @see saveThis()
     */
    public function addAction()
    {
        return $this->saveThis();
    }

    /**
     * Proxy method for save.
     *
     * @see saveThis()
     */
    public function editAction()
    {
        return $this->saveThis();
    }

    /**
     * Simple redirect to details action.
     *
     * @return \Zend\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirect()->toRoute(
            null,
            array('action' => 'index', $this->getIdentifierName() => $this->getIdentifier()),
            true
        );
    }

    /**
     * Responsible for saving the posted data.
     */
    public function saveThis()
    {
        $form = $this->generateFormWithData($this->getFormName(), $this->getFormCallback(), $this->getDataForForm());

        if ($this->getIsSaved()) {
            return $this->getResponse();
        }

        $view = $this->getView();

        $this->setPlaceholder('form', $form);

        $view->setTemplate('pages/crud-form');

        return $this->renderView($view);
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    public function processSave($data)
    {
        $result = parent::processSave($data);

        $this->addSuccessMessage('Saved successfully');

        $this->setIsSaved(true);

        if (func_num_args() > 1 && func_get_arg(1) === false /* redirect = false */) {
            return $result;
        }

        return $this->redirectToIndex();
    }

    /**
     * Simple redirect to index.
     */
    public function redirectToIndex()
    {
        return $this->redirectToRouteAjax(
            null,
            ['action'=>'index', $this->getIdentifierName() => null],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * Method checks that the required properties exist.
     *
     * @codeCoverageIgnore this is part of the event system.
     * @throws \LogicException
     * @return boolean
     */
    public function checkRequiredProperties()
    {
        $missingProperties = false;

        $classProperties = array_keys(get_object_vars($this));

        foreach ($this->requiredProperties as $requiredProperty) {

            if (!in_array($requiredProperty, $classProperties)) {

                $missingProperties[] = $requiredProperty;
            }
        }

        if ($missingProperties) {

            $message = 'Missing properties: ' . implode(', ', $missingProperties) . PHP_EOL;

            $message .= 'These properties are set: ' . implode(', ', $classProperties) . PHP_EOL;

            throw new \LogicException($message, null, null);
        }

        return true;
    }

    /**
     * Really useful method that gets us the view helper manager
     * from the service locator.
     *
     * @return ViewHelperManager
     */
    public function getViewHelperManager()
    {
        return $this->getServiceLocator()->get('viewHelperManager');
    }

    /**
     * Extend the render view method
     *
     * @param string|\Zend\View\Model\ViewModel $view
     * @param string|null $pageTitle
     * @param string|null $pageSubTitle
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $pageLayoutInner = $this->getPageLayoutInner();

        if (!is_null($pageLayoutInner)) {

            // This is a zend\view\variables object - cast it to an array.
            $layout = $this->getView((array)$view->getVariables());

            $layout->setTemplate($pageLayoutInner);

            $this->maybeAddScripts($layout);

            $layout->addChild($view, 'content');

            return parent::renderView($layout, $pageTitle, $pageSubTitle);
        }

        $this->maybeAddScripts($view);
        return parent::renderView($view, $pageTitle, $pageSubTitle);
    }

    /**
     * Sets the navigation to that secified in the controller. Useful for when a controller is
     * 100% reresented by a single navigation object.
     *
     * @see $this->navigationId
     *
     * @return boolean true
     */
    public function setNavigationCurrentLocation()
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        if (!empty($this->navigationId)) {
            $navigation->findOneBy('id', $this->navigationId)->setActive();
        }

        return true;
    }

    /**
     * Sets the navigation Id. Usually, this would be set as the default
     * value in a child controller, however, we need to re set it in places.
     *
     * @param unknown $navigationId
     * @return \Olcs\Controller\CrudAbstract
     */
    public function setNavigationId($navigationId)
    {
        $this->navigationId = $navigationId;
        return $this;
    }

    /**
     * Gets a variable from the route
     *
     * @param string $param
     * @param mixed $default
     * @return type
     * @deprecated
     */
    public function fromRoute($param, $default = null)
    {
        return $this->getFromRoute($param, $default);
    }

    /**
     * Gets a variable from postdata
     *
     * @param string $param
     * @param mixed $default
     * @return type
     * @deprecated
     */
    public function fromPost($param, $default = null)
    {
        return $this->getFromPost($param, $default);
    }

    /**
     * Replaces arrays with Ids, just with the value of the ID.
     *
     * @param array $array
     * @param array $ids
     *
     * @return array
     */
    public function replaceIds(array $array, array $ids)
    {
        foreach ($array as $key => $value) {
            if (in_array($key, $ids)) {
                if (is_array($value) && array_key_exists('id', $value)) {
                    $array[$key] = $value['id'];
                }
            }
        }

        return $array;
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        if (isset($data['id'])) {
            $bundle = $this->getDataBundle();

            if (isset($bundle['children'])) {
                $fields = array_keys($bundle['children']);
                $data = $this->replaceIds($data, $fields);
            }

            $data['fields'] = $data;
            $data['base'] = $data;
        } else {
            $caseId = $this->getQueryOrRouteParam('case');
            $data['case'] = $caseId;
            $data['fields']['case'] = $caseId;
            $data['base']['case'] = $caseId;
        }

        return $data;
    }

    /**
     * Comments box. We know there's a record here, so
     * there's no need to check for add / edit.
     */
    public function buildCommentsBoxIntoView()
    {
        if ($this->commentBoxName) {
            $form = $this->generateForm(
                'comment',
                'processCommentForm'
            );

            $case = $this->getCase();
            $data = [];
            $data['fields']['id'] = $case['id'];
            $data['fields']['version'] = $case['version'];
            $data['fields']['comment'] = $case[$this->commentBoxName];

            $form->setData($data);

            $this->setPlaceholder('comments', $form);
        }
    }

    /**
     * Setter for field name for comment box.
     *
     * @param string $commentBoxName
     *
     * @return \Olcs\Controller\CrudAbstract
     */
    public function setCommentBoxName($commentBoxName)
    {
        $this->commentBoxName = $commentBoxName;
        return $this;
    }

    /**
     * Proceses the comment box. We know there's a record here, so
     * there's no need to check for add / edit.
     *
     * @param array $data
     */
    public function processCommentForm($data)
    {
        $update = [];
        $update['id'] = $data['fields']['id'];
        $update['version'] = $data['fields']['version'];
        $update[$this->commentBoxName] = $data['fields']['comment'];

        $this->save($update, 'Cases');

        $this->addSuccessMessage('Comments updated successfully');

        $this->redirectToIndex();
    }

    /**
     * Load data for the form
     *
     * This method should be overridden
     *
     * @param int $id
     * @return array
     */
    public function load($id)
    {
        $existingData = $this->getLoadedData();

        if (empty($existingData)) {
            $service = $this->getService();

            $result = $this->makeRestCall(
                $service,
                'GET',
                array($this->getIdentifierKey() => $id),
                $this->getDataBundle()
            );

            if ($this->isListResult()) {

                if (!array_key_exists('Results', $result) || empty($result['Results'])) {
                    return [];
                }

                $this->setLoadedData(current($result['Results']));
            } else {
                if (empty($result)) {
                    $this->setCaughtResponse($this->notFoundAction());
                    return;
                }

                $this->setLoadedData($result);
            }
        }

        return $this->getLoadedData();
    }
}
