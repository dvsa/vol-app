<?php

namespace Permits\Controller;

use Common\Controller\Traits\GenericReceipt;
use Common\Controller\Traits\StoredCardsTrait;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveIssuedPermitsSummary;
use Common\RefData;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\View\Helper\IrhpApplicationSection;
use Zend\Http\Header\Referer as HttpReferer;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class PermitsController extends AbstractSelfserveController
{
    use ExternalControllerTrait;
    use GenericReceipt;
    use StoredCardsTrait;
    use FlashMessengerTrait;

    protected $applicationsTableName = 'dashboard-permit-application';
    protected $issuedTableName = 'dashboard-permits-issued';

    protected $currentMessages = [];

    /**
     * @todo This is just a placeholder, this will be implemented properly using system parameters in OLCS-20848
     *
     * @var array
     */
    protected $govUkReferrers = [];

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

        $response = $this->handleQuery(
            SelfserveApplicationsSummary::create(
                ['organisation' => $this->getCurrentOrganisationId()]
            )
        );

        $applicationData = $response->getResult();

        $response = $this->handleQuery(
            SelfserveIssuedPermitsSummary::create(
                ['organisation' => $this->getCurrentOrganisationId()]
            )
        );

        $issuedData = $response->getResult();

        $table = $this->getServiceLocator()->get('Table');
        $issuedTable = $table->prepareTable($this->issuedTableName, $this->alterDataForTable($issuedData));
        $applicationsTable = $table->prepareTable($this->applicationsTableName, $applicationData);

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
            if (in_array(
                $item['typeId'],
                [
                    RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                ]
            )) {
                // TODO - OLCS-25382
                // Certificate of Roadworthiness doesn't have valid permit count
                // set the value here for now
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
        $query = MyAccount::create([]);
        $response = $this->handleQuery($query)->getResult();

        return $response['eligibleForPermits'];
    }

    /**
     * Check whether the referrer is the gov.uk permits page
     *
     * @param MvcEvent $e
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
}
