<?php
/**
 * IRHP Permits Scoring Controller
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
namespace Admin\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\Permits\QueueAcceptScoring;
use Dvsa\Olcs\Transfer\Command\Permits\QueueRunScoring;
use Dvsa\Olcs\Transfer\Query\Permits\StockOperationsPermitted;
use Admin\Controller\AbstractIrhpPermitAdminController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class IrhpPermitScoringController extends AbstractIrhpPermitAdminController implements LeftViewProvider, ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::ADMIN_PERMITS
        ],
    ];

    protected $navigationId = 'admin-dashboard/admin-permits';
    protected $tableViewTemplate = 'pages/irhp-permit-scoring/index';

    protected $redirectConfig = [
        'accept' => [
            'action' => 'index'
        ],
        'run' => [
            'action' => 'index'
        ]
    ];

    protected $inlineScripts = [
        'indexAction' => ['permits-scoring']
    ];

    /**
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-permits',
                'navigationTitle' => '',
                'stockId' => $this->params()->fromRoute()['stockId']
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $visibilityResult = $this->handleQuery(
            StockOperationsPermitted::create([ 'id' => $this->params()->fromRoute('stockId') ])
        );

        $this->placeholder()->setPlaceholder('operationsPermitted', $visibilityResult->getResult());
        $this->placeholder()->setPlaceholder('stockId', $this->params()->fromRoute('stockId'));

        return $this->viewBuilder()->buildViewFromTemplate($this->tableViewTemplate);
    }

    /**
     * @return array|mixed|ViewModel
     */
    public function acceptAction()
    {
        return parent::confirmCommand(
            new ConfirmItem(['id' => 'stockId']),
            QueueAcceptScoring::class,
            'Accept scoring - are you sure?',
            'This will Accept scoring. Are you sure?',
            'Accept handler successfully triggered'
        );
    }

    /**
     * @return array|mixed|ViewModel
     */
    public function runAction()
    {
        return parent::confirmCommand(
            new ConfirmItem(['id' => 'stockId']),
            QueueRunScoring::class,
            'Run scoring - are you sure?',
            'This will run scoring. Are you sure?',
            'Scoring successfully triggered'
        );
    }

    /**
     * @return JsonModel
     */
    public function statusAction()
    {
        $stockOperationsPermitted = $this->handleQuery(
            StockOperationsPermitted::create([ 'id' => $this->params()->fromRoute('stockId') ])
        );

        return new JsonModel($stockOperationsPermitted->getResult());
    }
}
