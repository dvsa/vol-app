<?php

namespace Olcs\Controller\Licence;

use Common\Controller\Traits\CheckForCrudAction;
use Common\RefData;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\Cases\ByLicence as CasesByLicenceQry;
use Laminas\Http\Response;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva;

class LicenceController extends AbstractController implements LicenceControllerInterface
{
    use Lva\Traits\LicenceControllerTrait;
    use CheckForCrudAction;

    protected $lva;

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        protected OppositionHelperService $oppositionHelper,
        protected ComplaintsHelperService $complaintsHelper,
        protected $navigation,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct($scriptFactory, $formHelper, $tableFactory, $viewHelperManager);
        $this->flashMessengerHelper = $flashMessengerHelper;
    }

    /**
     * Cases
     *
     * @return ViewModel
     */
    public function casesAction()
    {
        $httpResponse = $this->checkForCrudAction('case', [], 'case');
        if ($httpResponse instanceof Response) {
            return $httpResponse;
        }

        $view = $this->getViewWithLicence();

        $params = [
            'licence' => $this->getQueryOrRouteParam('licence'),
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'createdOn, id'),
            'order'   => $this->getQueryOrRouteParam('order', 'DESC'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
        ];

        $params['query'] = $this->getRequest()->getQuery()->toArray();

        $response = $this->handleQuery(CasesByLicenceQry::create($params));
        $results = $response->getResult();

        // If this is an 'unlicensed' licence, redirect to the Unlicensed
        // Operator version of the page
        if ($results['extra']['licence']['status']['id'] === RefData::LICENCE_STATUS_UNLICENSED) {
            return $this->redirect()->toRoute(
                'operator-unlicensed/cases',
                ['organisation' => $results['extra']['organisation']['id']]
            );
        }

        $view->table = $this->getTable('cases', $results, $params);

        $view->setTemplate('pages/table');

        $this->loadScripts(['table-actions']);

        return $this->renderView($view);
    }

    /**
     * Opposition page
     *
     * @return ViewModel
     */
    public function oppositionAction()
    {
        $licenceId = (int) $this->params()->fromRoute('licence', null);

        $responseOppositions = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Opposition\OppositionList::create(
                [
                    'licence' => $licenceId,
                    'sort' => 'raisedDate',
                    'order' => 'ASC',
                    'page' => 1,
                    'limit' => 100,
                ]
            )
        );
        if (!$responseOppositions->isOk()) {
            throw new \RuntimeException('Cannot get Opposition list');
        }
        $oppositionResults = $responseOppositions->getResult()['results'];

        /* @var $oppositionHelperService \Common\Service\Helper\OppositionHelperService */
        $oppositionHelperService = $this->oppositionHelper;
        $oppositions = $oppositionHelperService->sortOpenClosed($oppositionResults);

        $responseComplaints = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\EnvironmentalComplaint\EnvironmentalComplaintList::create(
                [
                    'licence' => $licenceId,
                    'sort' => 'complaintDate',
                    'order' => 'ASC',
                    'page' => 1,
                    'limit' => 100,
                ]
            )
        );
        if (!$responseComplaints->isOk()) {
            throw new \RuntimeException('Cannot get Complaints list');
        }
        $casesResults = $responseComplaints->getResult()['results'];

        /* @var $complaintsHelperService \Common\Service\Helper\ComplaintsHelperService */
        $complaintsHelperService = $this->complaintsHelper;
        $complaints = $complaintsHelperService->sortCasesOpenClosed($casesResults);

        $view = new ViewModel(
            [
                'tables' => [
                    $this->getTable('opposition-readonly', $oppositions),
                    $this->getTable('environmental-complaints-readonly', $complaints)
                ]
            ]
        );
        $view->setTemplate('pages/multi-tables');

        return $this->renderView($view);
    }

    /**
     * This method is to assist the hierarchical nature of Laminas
     * navigation when parent pages need to also be siblings
     * from a breadcrumb and navigation point of view.
     *
     * @return \Laminas\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('licence/details/overview', [], [], true);
    }

    /**
     * Redirect to licence overview page by a licNo
     *
     * @return \Laminas\View\Helper\ViewModel
     */
    public function licNoAction()
    {
        $licNo = $this->params('licNo');
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Licence\LicenceByNumber::create(['licenceNumber' => $licNo])
        );

        if ($response->isOk()) {
            $licenceId = (int) $response->getResult()['id'];

            return $this->redirect()->toRoute('licence', ['licence' => $licenceId]);
        }

        return $this->notFoundAction();
    }
}
