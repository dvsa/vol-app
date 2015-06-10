<?php
/**
 * History Controller
 */
namespace Olcs\Controller\Cases\Processing;

use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits as CommonTraits;
use Zend\Mvc\MvcEvent as MvcEvent;

/**
 * History Controller
 */
class HistoryController extends AbstractActionController implements CaseControllerInterface, PageLayoutProvider, PageInnerLayoutProvider
{
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
    protected $pageLayout = 'layout/case-section';

    public function getPageLayout()
    {
        return $this->pageLayout;
    }

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

    public function getPageInnerLayout()
    {
        return $this->pageLayoutInner;
    }

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_history';

    protected $detailsView = 'pages/event-history';

    /**
     * These properties must be defined to use the GenericRenderView trait
     */
    protected $headerViewTemplate = 'partials/header';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'EventHistory';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case',
    ];

    protected $defaultTableSortField = 'eventDatetime';

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
            'page'    => $this->params()->fromQuery('page', 1),
            'sort'    => $this->params()->fromQuery('sort', $this->defaultTableSortField),
            'order'   => $this->params()->fromQuery('order', 'DESC'),
            'limit'   => $this->params()->fromQuery('limit', 10),
        ];

        foreach ($this->getListVars() as $varName) {
            $params[$varName] = $this->params()->fromRoute($varName);
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

    public function indexAction()
    {
        $response = $this->getListData();

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {

            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return $this->viewBuilder()->buildViewFromTemplate('pages/table-comments');
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

        return $this->viewBuilder()->buildViewFromTemplate('pages/table-comments');

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
    protected function getListData()
    {
        $params = $this->getListParams();

        $dto = new \Dvsa\Olcs\Transfer\Query\Processing\History();
        $dto->exchangeArray($params);

        return $this->handleQuery($dto);
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
}
