<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;

use Common\Controller\Traits\GenericReceipt;
use Common\Controller\Traits\StoredCardsTrait;
use Dvsa\Olcs\Transfer\Query\ContactDetail\CountrySelectList;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Common\Util\FlashMessengerTrait;

use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveIssuedPermitsSummary;

use Common\RefData;

use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\View\Helper\EcmtSection;

use Permits\View\Helper\IrhpApplicationSection;
use Zend\Http\Header\Referer as HttpReferer;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitFees;
use Zend\View\Model\ViewModel;

class PermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    use ExternalControllerTrait;
    use GenericReceipt;
    use StoredCardsTrait;
    use FlashMessengerTrait;

    const ECMT_APPLICATION_FEE_PRODUCT_REFENCE = 'IRHP_GV_APP_ECMT';
    const ECMT_ISSUING_FEE_PRODUCT_REFENCE = 'IRHP_GV_ECMT_100_PERMIT_FEE';

    protected $applicationsTableName = 'dashboard-permit-application';
    protected $issuedTableName = 'dashboard-permits-issued';

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

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

        $typesToGroupByLicence = [
            RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
            RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
            RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
            RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
        ];

        foreach ($data as $key => $item) {
            if (in_array($item['typeId'], $typesToGroupByLicence)) {
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
         * @var HttpRequest      $request
         * @var HttpReferer|bool $referer
         */
        $request = $e->getRequest();
        $referer = $request->getHeader('referer');

        if (!$referer instanceof HttpReferer) {
            return false;
        }

        return in_array($referer->getUri(), $this->govUkReferrers);
    }

    /**
     * Returns Issuing application fees
     *
     * @return array
     */
    private function getEcmtPermitFees()
    {
        $query = EcmtPermitFees::create(
            [
                'productReferences' => [
                    self::ECMT_APPLICATION_FEE_PRODUCT_REFENCE,
                    self::ECMT_ISSUING_FEE_PRODUCT_REFENCE
                ]
            ]
        );
        $response = $this->handleQuery($query);
        return $response->getResult();
    }
}
