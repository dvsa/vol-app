<?php
/**
 * History Controller
 */
namespace Olcs\Controller\Bus\Processing;

// Olcs
use Olcs\Controller\CrudAbstract;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Traits\CaseControllerTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits as CommonTraits;
use Zend\Mvc\MvcEvent as MvcEvent;

/**
 * History Controller
 */
class HistoryController extends AbstractActionController implements BusRegControllerInterface
{
    use CaseControllerTrait;
    use CommonTraits\GenericRenderView {
        CommonTraits\GenericRenderView::renderView as parentRenderView;
    }

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Identifier key
     *
     * @var string
     */
    protected $identifierKey = 'id';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = '';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'bus-registrations-section';

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/bus-registration-subsection';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'BusRegHistoryView';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_bus_processing_event-history';

    protected $detailsView = 'pages/event-history';

    protected $defaultTableSortField = 'eventDatetime';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'busRegId',
    ];

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
                'base',
            )
        )
    );

    /**
     * Holds the isAction
     *
     * @var boolean
     */
    protected $isAction = false;

    /**
     * Holds the table name
     *
     * @var string
     */
    protected $tableName = 'event-history';

    /**
     * These properties must be defined to use the GenericRenderView trait
     */
    protected $pageTitle = null;
    protected $pageSubTitle = null;
    protected $headerViewTemplate = 'partials/header';

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'eventHistoryType' => [],
            'user' => [
                'children' => [
                    'contactDetails' => [
                        'children' => [
                            'person' => [],
                        ]
                    ]
                ]
            ]
        )
    );

    /**
     * Contains the name of the view placeholder for the table.
     *
     * @var string
     */
    protected $tableViewPlaceholderName = 'table';

    /**
     * Holds any inline scripts for the current page
     *
     * @var array
     */
    protected $inlineScripts = [];

    /**
     * @codeCoverageIgnore this is part of the event system.
     */
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        if (method_exists($this, 'setNavigationCurrentLocation')) {
            $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'setNavigationCurrentLocation'), 6);
        }
    }

    public function getListParams()
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

        return $params;
    }

    public function getListParamsForTable()
    {
        $params = $this->getListParams();

        $params['query'] = $this->getRequest()->getQuery();

        return $params;
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

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('pages/table-comments');

        $response = $this->getListData();

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {

            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return $this->renderView($view);
        }

        if ($response->isOk()) {

            $tableName = $this->getTableName();

            $params = $this->getListParamsForTable();

            $data = $response->getResult();

            $this->setPlaceholder(
                $this->getTableViewPlaceholderName(),
                $this->getServiceLocator()->get('Table')->buildTable($tableName, $data, $params, false)
            );
        }

        return $this->renderView($view);
    }

    /**
     * Get table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return $this->tableName;
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

    /**
     * @return Response
     */
    public function getListData()
    {
        $params = $this->getListParams();

        $dto = new \Dvsa\Olcs\Transfer\Query\Processing\History();
        $dto->exchangeArray($params);

        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery($dto);

        return $this->getServiceLocator()->get('QueryService')->send($query);
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

        if (property_exists($this, 'navigationId')) {
            $this->setPlaceholder('navigationId', $this->navigationId);
        }

        if (!is_null($pageLayoutInner)) {

            // This is a zend\view\variables object - cast it to an array.
            $layout = $this->getView((array)$view->getVariables());

            $layout->setTemplate($pageLayoutInner);

            $this->maybeAddScripts($layout);

            $layout->addChild($view, 'content');

            return $this->parentRenderView($layout, $pageTitle, $pageSubTitle);
        }

        $this->maybeAddScripts($view);

        return $this->parentRenderView($view, $pageTitle, $pageSubTitle);
    }

    /**
     * @return string
     */
    public function getPageLayoutInner()
    {
        return $this->pageLayoutInner;
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
     * Gets a view model with optional params
     *
     * @param array $params
     * @return ViewModel
     */
    public function getView(array $params = null)
    {
        return new ViewModel($params);
    }

    /**
     * Get the inline scripts
     *
     * @return array
     */
    public function getInlineScripts()
    {
        return $this->inlineScripts;
    }

    /**
     * Optionally add scripts to view, if there are any
     *
     * @param ViewModel $view
     */
    protected function maybeAddScripts($view)
    {
        $scripts = $this->getInlineScripts();

        if (empty($scripts)) {
            return;
        }

        // this process defers to a service which takes care of checking
        // whether the script(s) exist
        $this->loadScripts($scripts);
    }

    /*
     * Load an array of script files which will be rendered inline inside a view
     *
     * @param array $scripts
     * @return array
     */
    protected function loadScripts($scripts)
    {
        return $this->getServiceLocator()->get('Script')->loadFiles($scripts);
    }

    /**
     * Sets the navigation to that specified in the controller. Useful for when a controller is
     * 100% represented by a single navigation object.
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
}
