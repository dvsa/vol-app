<?php
/**
 * History Controller
 */
namespace Olcs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent as MvcEvent;

// for type hints
use Olcs\View\Builder\BuilderInterface as ViewBuilderInterface;
use Olcs\Mvc\Controller\Plugin;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Common\Service\Cqrs\Response;

/**
 * Abstract class to extend for BASIC list/edit/delete functions
 *
 * @method ViewBuilderInterface viewBuilder()
 * @method Plugin\Script script()
 * @method Plugin\Placeholder placeholder()
 * @method Plugin\Table table()
 * @method Response handleQuery(QueryInterface $query)
 */
abstract class AbstractInternalController extends AbstractActionController
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = '';

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

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'partials/table';
    protected $defaultTableSortField = 'id';
    protected $tableName = '';
    protected $listDto = '';
    protected $listVars = [];

    private function getListParams()
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

    /**
     * Variables for controlling details view rendering
     * details view template and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/case/offence';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = '';
    protected $itemParams = ['id'];

    /**
     * Method to display details of a legacy offence
     * @return array|ViewModel
     */
    public function detailsAction()
    {
        $dto = $this->itemDto;
        $itemParams = $this->getItemParams();

        $response = $this->handleQuery($dto::create($itemParams));

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                $this->setPlaceholder($this->detailsViewPlaceholderName, $data);
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }

    /**
     * Gets a single legacy offence by case and legacy offence ID
     * @return Response
     */
    private function getItemParams()
    {
        $params = [];

        foreach ((array) $this->itemParams as $varName) {
            $params[$varName] = $this->params()->fromRoute($varName);
        }

        return $params;
    }
}
