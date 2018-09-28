<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;

use Common\Controller\Traits\GenericReceipt;
use Common\Controller\Traits\StoredCardsTrait;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtLicence;

use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\NextIrhpPermitStock;
use Dvsa\Olcs\Transfer\Query\Organisation\EligibleForPermits;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Query\Permits\ById;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitApplication;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtCountriesList;
use Dvsa\Olcs\Transfer\Query\Permits\LastOpenWindow;
use Dvsa\Olcs\Transfer\Query\Permits\OpenWindows;

use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;

use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCountries;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtPermitsRequired;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtTrips;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateInternationalJourney;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateSector;

use Common\RefData;

use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\View\Helper\EcmtSection;

use Zend\Http\Header\Referer as HttpReferer;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitFees;
use Zend\View\Model\ViewModel;

use DateTime;

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
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

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
                return $this->notFoundAction();
            }
            return $view;
        }

        $query = EcmtPermitApplication::create(
            [
                'order' => 'DESC',
                'organisation' => $this->getCurrentOrganisationId(),
                'statusIds' => [
                    RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                    RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                    RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                ],
            ]
        );
        $response = $this->handleQuery($query);
        $applicationData = $response->getResult();

        $applicationsTable = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable($this->applicationsTableName, $applicationData['results']);

        $query = EcmtPermitApplication::create(
            [
                'order' => 'DESC',
                'organisation' => $this->getCurrentOrganisationId(),
                'statusIds' => [RefData::PERMIT_APP_STATUS_VALID]
            ]
        );
        $response = $this->handleQuery($query);
        $issuedData = $response->getResult();

        $issuedTable = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable($this->issuedTableName, $issuedData['results']);

        $view->setVariable('isEligible', $eligibleForPermits);
        $view->setVariable('issuedNo', $issuedData['count']);
        $view->setVariable('applicationsNo', $applicationData['count']);
        $view->setVariable('applicationsTable', $applicationsTable);
        $view->setVariable('issuedTable', $issuedTable);

        return $view;
    }

    public function addAction()
    {
        $currentDateTime = new DateTime();
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

        $response = $this->handleQuery(
            OpenWindows::create(['currentDateTime' => $formattedDateTime])
        );
        $result = $this->handleResponse($response);

        if (count($result['windows']) == 0) {
            $response = $this->handleQuery(
                LastOpenWindow::create(['currentDateTime' => $formattedDateTime])
            );
            $result = $this->handleResponse($response);

            $view = new ViewModel();
            $view->setVariable('lastOpenWindowDate', $result['results']['endDate']);
            $view->setTemplate('permits/window-closed');

            return $view;
        }

        $form = $this->getForm('EcmtLicenceForm');
        $query = NextIrhpPermitStock::create(['permitType' => 'permit_ecmt']);
        $stock = $this->handleQuery($query)->getResult();

        $view = new ViewModel(['form' => $form, 'stock' => $stock]);
        $view->setTemplate('permits/ecmt-licence');

        $data = $this->params()->fromPost();
        if (isset($data['Submit']['SubmitButton'])) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                $command = CreateEcmtPermitApplication::create(['licence'=> $data['Fields']['EcmtLicence']]);
                    $response = $this->handleCommand($command);
                    $insert = $response->getResult();

                    $this->redirect()
                        ->toRoute('permits/' . EcmtSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $insert['id']['ecmtPermitApplication']]);
            } else {
                $form->get('Fields')->get('EcmtLicence')->setMessages(['isEmpty' => "error.messages.ecmt-licence"]);
            }
        }

        return $view;
    }

    public function ecmtLicenceAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        $query = NextIrhpPermitStock::create(['permitType' => $application['permitType']['id']]);
        $stock = $this->handleQuery($query)->getResult();

        $form = $this->getForm('EcmtLicenceForm');

        $view = new ViewModel(['form' => $form, 'application' => $application, 'stock' => $stock]);
        $view->setTemplate('permits/ecmt-licence');

        $data = $this->params()->fromPost();

        if (isset($data['Submit']['SubmitButton'])) {
            $form->setData($data);
            if ($form->isValid()) {
                $this->redirect()
                    ->toRoute(
                        'permits/' . EcmtSection::ROUTE_ECMT_CONFIRM_CHANGE,
                        ['id' => $this->params()->fromRoute('id', -1)],
                        [ 'query' => [
                            'licenceId' => $data['Fields']['EcmtLicence']
                        ]]
                    );
            } else {
                $form->get('Fields')->get('EcmtLicence')->setMessages(['isEmpty' => "error.messages.ecmt-licence"]);
            }
        }

        return $view;
    }

    public function restrictedCountriesAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('RestrictedCountriesForm');

        // Read data
        $application = $this->getApplication($id);

        if (!is_null($application['hasRestrictedCountries'])) {
            $restrictedCountries = $application['hasRestrictedCountries'] == true ? 1 : 0;

            $form->get('Fields')
                ->get('restrictedCountries')
                ->setValue($restrictedCountries);
        }

        if (count($application['countrys']) > 0) {
            //Format results from DB before setting values on form
            $selectedValues = array();

            foreach ($application['countrys'] as $country) {
                $selectedValues[] = $country['id'];
            }

            $form->get('Fields')
                ->get('restrictedCountriesList')
                ->get('restrictedCountriesList')
                ->setValue($selectedValues);
        }

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                //EXTRA VALIDATION
                if ((
                    $data['Fields']['restrictedCountries'] == 1
                    && isset($data['Fields']['restrictedCountriesList']['restrictedCountriesList']))
                    || ($data['Fields']['restrictedCountries'] == 0)
                ) {
                    if ($data['Fields']['restrictedCountries'] == 0) {
                        $countryIds = [];
                    } else {
                        $countryIds = $data['Fields']['restrictedCountriesList']['restrictedCountriesList'];
                    }

                    $command = UpdateEcmtCountries::create(['id' => $id, 'countryIds' => $countryIds]);
                    $this->handleCommand($command);
                    $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_NO_OF_PERMITS);
                } else {
                    //conditional validation failed, restricted countries list should not be empty
                    $form->get('Fields')
                        ->get('restrictedCountriesList')
                        ->get('restrictedCountriesList')
                        ->setMessages(['error.messages.restricted.countries.list']);
                }
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('restrictedCountries')
                    ->setMessages(['error.messages.restricted.countries']);
            }
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function tripsAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        $trafficArea = $application['licence']['trafficArea'];
        $trafficAreaName = $trafficArea['name'];

        $licenceTrafficArea = $application['licence']['licNo'] . ' (' . $trafficAreaName . ')';
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $tripsHint = $translationHelper->translateReplace('permits.page.trips.form.hint', [$licenceTrafficArea]);

        //Create form from annotations
        $form = $this->getForm('TripsForm');

        $existing['Fields']['tripsAbroad'] = $application['trips'];
        $existing['Fields']['intensityWarning'] = 'no';
        $form->setData($existing);

        $data = $this->params()->fromPost();

        if (!empty($data)) {
            //Validate
            $form->setData($data);

            if ($form->isValid()) {
                $command = UpdateEcmtTrips::create(['id' => $id, 'ecmtTrips' => $data['Fields']['tripsAbroad']]);
                $this->handleCommand($command);
                if ($this->isHighIntensity($application, $data['Fields']['tripsAbroad']) && $data['Fields']['intensityWarning'] === 'no') {
                    $form->get('Fields')->get('intensityWarning')->setValue('yes');
                } else {
                    $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY);
                }
            }
        }

        return array(
            'form' => $form,
            'ref' => $application['applicationRef'],
            'id' => $id,
            'isNI' => $this->isNi($application)
        );
    }


    // Understand this is a computer property but added to avoid a round-trip to API for simple arithmetic operation.
    protected function isHighIntensity($application, $tripsAbroad)
    {
        if (isset($application['permitsRequired'])) {
            return ($tripsAbroad / $application['permitsRequired']) > 100;
        } else {
            return false;
        }
    }



    public function internationalJourneyAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);
        $trafficArea = $application['licence']['trafficArea'];

        //Create form from annotations
        $form = $this->getForm('InternationalJourneyForm');

        // read data
        $form->get('Fields')->get('InternationalJourney')->setValue($application['internationalJourneys']);
        $form->get('Fields')->get('intensityWarning')->setValue('no');

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);

            if ($form->isValid()) {
                $commandData = [
                    'id' => $id,
                    'internationalJourney' => $data['Fields']['InternationalJourney'],
                ];
                $command = UpdateInternationalJourney::create($commandData);
                $this->handleCommand($command);
                if ($data['Fields']['InternationalJourney'] === RefData::ECMT_APP_JOURNEY_OVER_90 && $data['Fields']['intensityWarning'] === 'no') {
                    $form->get('Fields')->get('intensityWarning')->setValue('yes');
                } else {
                    $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_SECTORS);
                }
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('InternationalJourney')
                    ->setMessages(['error.messages.international-journey']);
            }
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef'], 'trafficAreaId' => $trafficArea['id']);
    }

    public function sectorAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('SpecialistHaulageForm');

        // Read data
        $application = $this->getApplication($id);

        if (isset($application)) {
            if (isset($application['sectors'])) {
                //Format results from DB before setting values on form
                $selectedValue = $application['sectors']['id'];

                $form->get('Fields')
                    ->get('SectorList')
                    ->setValue($selectedValue);
            }
        }

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                    $sectorID = $data['Fields']['SectorList'];
                    $command = UpdateSector::create(['id' => $id, 'sector' => $sectorID]);

                    $this->handleCommand($command);

                    $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_CHECK_ANSWERS);
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('SectorList')
                    ->setMessages(['error.messages.sector.list']);
            }
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function permitsRequiredAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        $ecmtPermitFees = $this->getEcmtPermitFees();
        $ecmtApplicationFee =  $ecmtPermitFees['fee'][$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE]['fixedValue'];

        //Create form from annotations
        $form = $this->getForm('PermitsRequiredForm');

        $existing['Fields']['permitsRequired'] = $application['permitsRequired'];
        $form->setData($existing);

        $data = $this->params()->fromPost();

        if (!empty($data)) {
            $data['Fields']['numVehicles'] = $application['licence']['totAuthVehicles'];

            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                $command = UpdateEcmtPermitsRequired::create(
                    [
                        'id' => $id,
                        'permitsRequired' => $data['Fields']['permitsRequired']
                    ]
                );
                $response = $this->handleCommand($command);

                $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_TRIPS);
            }
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $totalVehicles = $translationHelper->translateReplace('permits.form.permits-required.hint', [$application['licence']['totAuthVehicles']]);
        $form->get('Fields')->get('permitsRequired')->setOption('hint', $totalVehicles);

        $guidanceMessage = $translationHelper->translateReplace('permits.form.permits-required.fee.guidance', ['£' . $ecmtApplicationFee]);

        return array('form' => $form, 'guidanceMessage' => $guidanceMessage, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function changeLicenceAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        $request = $this->getRequest();
        $data = (array)$request->getPost();
        $application = $this->getApplication($id);

        //Create form from annotations
        $form = $this->getForm('ChangeLicenceForm');
        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);

            if ($form->isValid()) {
                $command = UpdateEcmtLicence::create(['id' => $id, 'licence' => $data['Fields']['licenceId']]);
                $response = $this->handleCommand($command);
                $insert = $response->getResult();
                $this->redirect()
                    ->toRoute('permits/' . EcmtSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $id]);
            }
        }

        $formData['Fields']['licenceId'] = $this->getRequest()->getQuery('licenceId');
        $form->setData($formData);

        $view = new ViewModel();

        $view->setVariable('form', $form);
        $view->setVariable('id', $id);
        $view->setVariable('ref', $application['applicationRef']);

        return $view;
    }

    public function ecmtGuidanceAction()
    {
        $query = EcmtCountriesList::create(['isEcmtState' => 1]);
        $response = $this->handleQuery($query);
        $ecmtCountries = $response->getResult();

        // Get Fee Data
        $ecmtPermitFees = $this->getEcmtPermitFees();
        $ecmtApplicationFee =  $ecmtPermitFees['fee'][$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE]['fixedValue'];
        $ecmtIssuingFee = $ecmtPermitFees['fee'][$this::ECMT_ISSUING_FEE_PRODUCT_REFENCE]['fixedValue'];

        $view = new ViewModel();
        $view->setVariable('ecmtCountries', $ecmtCountries['results']);
        $view->setVariable('applicationFee', $ecmtApplicationFee);
        $view->setVariable('issueFee', $ecmtIssuingFee);
        return $view;
    }

    /**
     * Page displayed when from the Permit Dashboard
     * the user clicks the Reference of an application
     * in status 'Under Consideration'.
     *
     * From this page the user may or may not be given the
     * opportunity to withdraw the application.
     *
     */
    public function underConsiderationAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        if (!$application['isUnderConsideration']) {
            return $this->conditionalDisplayNotMet();
        }

        $ecmtPermitFees = $this->getEcmtPermitFees();
        $ecmtApplicationFee =  $ecmtPermitFees['fee'][$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE]['fixedValue'];
        $ecmtApplicationFeeTotal = $ecmtApplicationFee * $application['permitsRequired'];

        /**
         * @todo status view helper and table config shouldn't be in the controller
         * @var \Common\View\Helper\Status $statusHelper
         */
         $statusHelper = $this->getServiceLocator()->get('ViewHelperManager')->get('status');

         $tableData = array(
             'results' => array(
                 0 => array(
                     'applicationDetailsTitle' => 'permits.page.ecmt.consideration.application.status',
                     'applicationDetailsAnswer' => $statusHelper->__invoke($application['status'])
                 ),
                 1 => array(
                     'applicationDetailsTitle' => 'permits.page.ecmt.consideration.permit.type',
                     'applicationDetailsAnswer' => $application['permitType']['description']
                 ),
                 2 => array(
                     'applicationDetailsTitle' => 'permits.page.ecmt.consideration.reference.number',
                     'applicationDetailsAnswer' => $application['applicationRef']
                 ),
                 3 => array(
                     'applicationDetailsTitle' => 'permits.page.ecmt.consideration.application.date',
                     'applicationDetailsAnswer' => date(\DATE_FORMAT, strtotime($application['dateReceived']))
                 ),
                 4 => array(
                     'applicationDetailsTitle' => 'permits.page.ecmt.consideration.permits.required',
                     'applicationDetailsAnswer' => $application['permitsRequired']
                 ),
                 5 => array(
                     'applicationDetailsTitle' => 'permits.page.ecmt.consideration.application.fee',
                     'applicationDetailsAnswer' => '£' . $ecmtApplicationFeeTotal
                 )
             )
         );

        /** @var \Common\Service\Table\TableBuilder $table */
        $table = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable('under-consideration', $tableData);

        $view = new ViewModel();
        $view->setVariable('application', $application);
        $view->setVariable('table', $table);
        $view->setVariable('responseDate', '30 November 2018'); /** @todo this needs to be a system parameter */

        return $view;
    }

    /**
     * @return ViewModel
     */
    public function paymentAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $redirectUrl = $this->url()->fromRoute('permits/payment-result', ['id' => $id, 'reference' => $this->params()->fromQuery('receipt_reference')], ['force_canonical' => true]);

        $dtoData = [
            'cpmsRedirectUrl' => $redirectUrl,
            'ecmtPermitApplicationId' => $id,
            'paymentMethod' =>  RefData::FEE_PAYMENT_METHOD_CARD_ONLINE
        ];
        $dto = PayOutstandingFees::create($dtoData);
        $response = $this->handleCommand($dto);

        // Look up the new payment in order to get the redirect data
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();

        $view = new ViewModel(
            [
                'gateway' => $payment['gatewayUrl'],
                'data' => [
                    'receipt_reference' => $payment['reference']
                ]
            ]
        );
        // render the gateway redirect
        $view->setTemplate('cpms/payment');
        return $this->render($view);
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
        $query = EligibleForPermits::create([]);
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

    /**
     * Returns an application entry by id
     *
     * @param number $id application id
     * @return array
     */
    private function getApplication($id)
    {
        $query = ById::create(['id'=>$id]);
        $response = $this->handleQuery($query);

        return $response->getResult();
    }

    /**
     * Returns Issuing application fees
     *
     * @return array
     */
    private function getEcmtPermitFees()
    {
        $query = EcmtPermitFees::create(['productReferences' => [$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE, $this::ECMT_ISSUING_FEE_PRODUCT_REFENCE]]);
        $response = $this->handleQuery($query);
        return $response->getResult();
    }
}
