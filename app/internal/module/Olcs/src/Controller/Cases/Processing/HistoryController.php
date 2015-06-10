<?php
/**
 * History Controller
 */
namespace Olcs\Controller\Cases\Processing;

use Dvsa\Olcs\Transfer\Query\Processing\History;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits as CommonTraits;
use Zend\Mvc\MvcEvent as MvcEvent;

// for type hints
use Olcs\View\Builder\BuilderInterface as ViewBuilderInterface;
use Olcs\Mvc\Controller\Plugin;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Common\Service\Cqrs\Response;

/**
 * History Controller
 * @method ViewBuilderInterface viewBuilder()
 * @method Plugin\Script script()
 * @method Plugin\Placeholder placeholder()
 * @method Response handleQuery(QueryInterface $query)
 */
class HistoryController extends AbstractActionController
    implements CaseControllerInterface, PageLayoutProvider, PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case',
    ];

    protected $defaultTableSortField = 'eventDatetime';

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

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/case-details-subsection';
    }

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
        $response = $this->handleQuery(History::create($this->getListParams()));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return $this->viewBuilder()->buildViewFromTemplate('pages/table-comments');
        }

        if ($response->isOk()) {
            $params = $this->getListParamsForTable();
            $data = $response->getResult();

            $this->placeholder()->setPlaceholder(
                $this->tableViewPlaceholderName,
                $this->getServiceLocator()->get('Table')->buildTable($this->tableName, $data, $params, false)
            );
        }

        return $this->viewBuilder()->buildViewFromTemplate('pages/table-comments');
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
