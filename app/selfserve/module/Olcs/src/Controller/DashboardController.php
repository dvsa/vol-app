<?php

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Table\DataMapper\DashboardTmApplications;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\DvsaReports\GetRedirect as GetReportRedirectCmd;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Dvsa\Olcs\Transfer\Query\Organisation\Dashboard as DashboardQry;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Authentication\Storage\Session;
use Laminas\View\Model\ViewModel;
use Olcs\Service\Processing\DashboardProcessingService;
use Olcs\View\Model\Dashboard;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DashboardController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait;

    protected $lva = "application";

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected Session $session,
        protected DashboardProcessingService $dashboardProcessingService,
        protected DashboardTmApplications $dashboardTmApplicationsDataMapper,
        protected TableFactory $tableFactory
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * POST required data to DVSA Reports URL, handle response and perform redirect.
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function topsreportAction()
    {
        $dashboardData = $this->getDashboardData($this->getCurrentOrganisationId());

        $licenceNumbers = [];
        foreach ($dashboardData['licences'] as $licence) {
            $licenceNumbers[] = $licence['licNo'];
        }

        $session = $this->session->read();
        $redirectCmd = GetReportRedirectCmd::create(
            [
                'olNumbers' => $licenceNumbers,
                'jwt' => $session['AccessToken'],
                'refreshToken' => $session['RefreshToken']]
        );
        $response = $this->handleCommand($redirectCmd);

        if (!$response->isOk()) {
            $this->flashMessenger()->addErrorMessage($response->getResult()['messages'][0]);
            return $this->redirect()->toRoute('dashboard', [], [], true);
        }

        $messages = $response->getResult()['messages'];

        $view = new \Laminas\View\Model\ViewModel();
        $view->setTemplate('top-redirect');
        $view->setVariable('redirectUrl', $messages[0]);

        return $view;
    }

    /**
     * Dashboard index action
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        if (
            $this->isGranted(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
            !$this->isGranted(RefData::PERMISSION_SELFSERVE_LVA)
        ) {
            $view = $this->transportManagerDashboardView();
        } else {
            $view = $this->standardDashboardView();
        }

        return $view;
    }

    /**
     * Get the Standard Dashboard view
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    protected function standardDashboardView()
    {
        $organisationId = $this->getCurrentOrganisationId();

        if (empty($organisationId)) {
            $this->flashMessenger()->addErrorMessage('auth.login.failed.reason.account-disabled');
            return $this->redirect()->toRoute('auth/login/GET');
        }
        $dashboardData = $this->getDashboardData($organisationId);
        $total = 0;

        if (
            isset($dashboardData['licences'])
            && isset($dashboardData['applications'])
            && isset($dashboardData['variations'])
        ) {
            $total = count($dashboardData['licences'])
                + count($dashboardData['applications'])
                + count($dashboardData['variations']);
        }

        // build tables
        $params = $this->dashboardProcessingService->getTables($dashboardData);

        $params['total'] = $total;
        $params['showVariationTable'] = count($dashboardData['variations']) > 0;
        $params['showApplicationTable'] = count($dashboardData['applications']) > 0;

        // setup view
        $view = new \Laminas\View\Model\ViewModel($params);
        $view->setTemplate('dashboard');
        $view->setVariable('numberOfLicences', count($dashboardData['licences']));
        $view->setVariable('numberOfApplications', count($dashboardData['applications']));
        $view->setVariable('niFlag', $this->isNiFlagTrue($dashboardData));
        if (
            $this->handleQuery(IsEnabledQry::create(['ids' => [FeatureToggle::TOP_REPORTS_LINK]]))->getResult()['isEnabled']
            && (isset($dashboardData['licences']) && !empty($dashboardData['licences']))
        ) {
            $view->setVariable('topReportsLink', $this->url()->fromRoute('dashboard/topsreport'));
        }

        return $view;
    }

    /**
     * Perform dashboard data Qry
     */
    protected function getDashboardData(?int $organisationId)
    {
        // retrieve data
        $query = DashboardQry::create(['id' => $organisationId]);
        $response = $this->handleQuery($query);
        return $response->getResult()['dashboard'];
    }

    private function isNiFlagTrue($dashboardData): bool
    {
        $licencesApplications = array_merge($dashboardData['licences'], $dashboardData['applications']);
        $niFlags = array_filter(array_column($licencesApplications, 'niFlag'), fn($niFlag) => $niFlag === 'Y');
        return count($niFlags) >= 1;
    }

    /**
     * Get the Dashboard view for a Transport Manager
     *
     * @return ViewModel
     */
    protected function transportManagerDashboardView()
    {
        $userId = $this->currentUser()->getUserData()['id'];

        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(
                [
                    'user' => $userId,
                    'appStatuses' => [
                        RefData::APPLICATION_STATUS_UNDER_CONSIDERATION,
                        RefData::APPLICATION_STATUS_NOT_SUBMITTED
                    ],
                    'filterByOrgUser' => true
                ]
            )
        );
        $results = $response->getResult()['results'];

        // flatten the array
        $data = $this->dashboardTmApplicationsDataMapper->map($results);

        // create table
        $table = $this->tableFactory->buildTable('dashboard-tm-applications', $data);

        // setup view
        $view = new \Laminas\View\Model\ViewModel();
        $view->setTemplate('dashboard-tm');
        $view->setVariable('applicationsTable', $table);

        $this->placeholder()->setPlaceholder('pageTitle', 'dashboard.tm.title');

        return $view;
    }
}
