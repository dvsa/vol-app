<?php

namespace Permits\Controller;

use Common\Controller\Traits\GenericReceipt;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveIssuedPermitsSummary;
use Laminas\Http\Header\Referer as HttpReferer;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class PermitsController extends AbstractSelfserveController
{
    use ExternalControllerTrait;
    use GenericReceipt;
    use FlashMessengerTrait;

    protected $applicationsTableName = 'dashboard-permit-application';
    protected $issuedTableName = 'dashboard-permits-issued';

    protected $currentMessages = [];

    protected $lva;

    /**
     * @var array
     */
    protected $govUkReferrers = [];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @return ViewModel|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        $eligibleForPermits = $this->isEligibleForPermits();

        $view = new ViewModel();
        if (!$eligibleForPermits) {
            if (!$this->referredFromGovUkPermits($this->getEvent())) {
                return $this->nextStep(IrhpApplicationSection::ROUTE_NOT_ELIGIBLE);
            }
            return $view;
        }

        $orgId = $this->getCurrentOrganisationId();

        $response = $this->handleQuery(
            SelfserveApplicationsSummary::create(
                ['organisation' => $orgId]
            )
        );

        $applicationData = $response->getResult();

        $response = $this->handleQuery(
            SelfserveIssuedPermitsSummary::create(
                ['organisation' => $orgId]
            )
        );

        $issuedData = $response->getResult();

        $issuedTable = $this->tableBuilder->prepareTable($this->issuedTableName, $this->alterDataForTable($issuedData));
        $applicationsTable = $this->tableBuilder->prepareTable($this->applicationsTableName, $applicationData);

        $this->placeholder()->setPlaceholder('pageTitle', 'permits.page.dashboard.browser.title');

        $view->setVariable('isEligible', $eligibleForPermits);
        $view->setVariable('issuedNo', count($issuedData));
        $view->setVariable('issuedTable', $issuedTable);
        $view->setVariable('applicationsNo', count($applicationData));
        $view->setVariable('applicationsTable', $applicationsTable);

        return $view;
    }

    /**
     * Alter data for table
     *
     * @param array $data Data
     *
     * @return array
     */
    private function alterDataForTable(array $data)
    {
        $keys = [];

        foreach ($data as $key => $item) {
            if (
                in_array(
                    $item['typeId'],
                    [
                    RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    ]
                )
            ) {
                $data[$key]['validPermitCount'] = $item['validPermitCount'] = 1;
            }

            // group applications into one row per licence
            if (isset($keys[$item['licenceId']][$item['typeId']])) {
                // add number of permits required to the existing row
                $existingKey = $keys[$item['licenceId']][$item['typeId']];

                $data[$existingKey]['validPermitCount'] += $item['validPermitCount'];

                // remove this line altogether
                unset($data[$key]);
            } else {
                $keys[$item['licenceId']][$item['typeId']] = $key;
            }
        }

        return $data;
    }

    /**
     * Attach messages to display in the current response
     *
     * @return void
     */
    protected function attachCurrentMessages()
    {
        foreach ($this->currentMessages as $namespace => $messages) {
            foreach ($messages as $message) {
                $this->addMessage($message, $namespace);
            }
        }
    }

    /**
     * Whether the organisation is eligible for permits
     *
     * @return bool
     */
    private function isEligibleForPermits(): bool
    {
        $response = $this->getCurrentUser();
        return $response['eligibleForPermits'] ?? false;
    }

    /**
     * Check whether the referrer is the gov.uk permits page
     *
     *
     * @return bool
     */
    private function referredFromGovUkPermits(MvcEvent $e): bool
    {
        /**
         * @var HttpRequest $request
         * @var HttpReferer|bool $referer
         */
        $request = $e->getRequest();
        $referer = $request->getHeader('referer');

        if (!$referer instanceof HttpReferer) {
            return false;
        }

        return in_array($referer->getUri(), $this->govUkReferrers);
    }

    protected function checkForRedirect($lvaId)
    {
        return null;
    }
}
