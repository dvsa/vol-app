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
 * @method Plugin\Table table()
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
    protected $listVars = ['case'];

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

    protected $tableViewTemplate = 'pages/table-comments';

    protected $listDto = History::class;

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

    public function getListParams()
    {
        $params = [
            'page'    => $this->params()->fromQuery('page', 1),
            'sort'    => $this->params()->fromQuery('sort', $this->defaultTableSortField),
            'order'   => $this->params()->fromQuery('order', 'DESC'),
            'limit'   => $this->params()->fromQuery('limit', 10),
        ];

        foreach ((array) $this->listVars as $varName) {
            $params[$varName] = $this->params()->fromRoute($varName);
        }

        return $params;
    }

    public function indexAction()
    {
        $listParams = $this->getListParams();
        $dto = $this->listDto;
        $response = $this->handleQuery($dto::create($listParams));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return $this->viewBuilder()->buildViewFromTemplate($this->tableViewTemplate);
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            $this->placeholder()->setPlaceholder(
                $this->tableViewPlaceholderName,
                $this->table()->buildTable($this->tableName, $data, $listParams)
            );
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->tableViewTemplate);
    }
}
