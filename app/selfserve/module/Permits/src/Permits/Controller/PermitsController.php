<?php
namespace Permits\Controller;

use Common\Controller\AbstractOlcsController;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\Form\Form;

use Dvsa\Olcs\Transfer\Command\Permits\UpdateDeclaration;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCheckAnswers;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtLicence;

use Dvsa\Olcs\Transfer\Query\Organisation\EligibleForPermits;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Query\Permits\ById;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitApplication;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermits;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtCountriesList;

use Dvsa\Olcs\Transfer\Command\Permits\CancelEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\WithdrawEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;

use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCabotage;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtEmissions;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCountries;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtPermitsRequired;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtTrips;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateInternationalJourney;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateSector;
use Dvsa\Olcs\Transfer\Command\Permits\EcmtSubmitApplication;

use Common\RefData;

use Olcs\Controller\Lva\Traits\ExternalControllerTrait;

use Permits\View\Helper\EcmtSection;

use Zend\Http\Header\Referer as HttpReferer;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitFees;
use Zend\Session\Container; // We need this when using sessions
use Zend\View\Model\ViewModel;

class PermitsController extends AbstractOlcsController implements ToggleAwareInterface
{
    use ExternalControllerTrait;

    // TODO: Add event for all checks for whether or not $data(from form) is an array
    const SESSION_NAMESPACE = 'permit_application';
    const DEFAULT_SEPARATOR = '|';

    const ECMT_APPLICATION_FEE_PRODUCT_REFENCE = 'IRHP_GV_APP_ECMT';
    const ECMT_ISSUING_FEE_PRODUCT_REFENCE = 'IRHP_GV_ECMT_100_PERMIT_FEE';

    protected $applicationsTableName = 'dashboard-permit-application';
    protected $issuedTableName = 'dashboard-permits';

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::SELFSERVE_ECMT
        ],
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
                'organisationId' => $this->getCurrentOrganisationId(),
                'statusIds' => [RefData::ECMT_APP_STATUS_NOT_YET_SUBMITTED, RefData::ECMT_APP_STATUS_UNDER_CONSIDERATION, RefData::ECMT_APP_STATUS_AWAITING_FEE]
            ]
        );
        $response = $this->handleQuery($query);
        $applicationData = $response->getResult();

        $query = EcmtPermits::create([]);
        $response = $this->handleQuery($query);
        $issuedData = $response->getResult();

        $applicationsTable = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable($this->applicationsTableName, $applicationData['results']);

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
        $form = $this->getForm('EcmtLicenceForm');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('permits/ecmt-licence');

        $data = $this->params()->fromPost();
        if (isset($data['Fields']['SubmitButton'])) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                $command = CreateEcmtPermitApplication::create(['licence'=> $data['Fields']['EcmtLicence']]);
                    $response = $this->handleCommand($command);
                    $insert = $response->getResult();

                    $this->redirect()
                        ->toRoute('permits/' . EcmtSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $insert['id']['ecmtPermitApplication']]);
            }
        }

        return $view;
    }


    public function ecmtLicenceAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        $form = $this->getForm('EcmtLicenceForm');

        $view = new ViewModel(['form' => $form, 'application' => $application]);
        $view->setTemplate('permits/ecmt-licence');

        $data = $this->params()->fromPost();
        if (isset($data['Fields']['SubmitButton'])) {
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
            }
        }

        return $view;
    }

    public function applicationOverviewAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        // Get Fee Data
        $ecmtFees = $this->getEcmtPermitFees();
        $applicationFee = $ecmtFees['fee'][$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE]['fixedValue'];
        $applicationFeeTotal = $applicationFee * $application['permitsRequired'];

        $view = new ViewModel();
        $view->setVariable('id', $id);
        $view->setVariable('applicationFee', $applicationFee);
        $view->setVariable('totalFee', $applicationFeeTotal);
        $view->setVariable('application', $application);

        return $view;
    }

    public function euro6EmissionsAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('Euro6EmissionsForm');

        // read data
        $application = $this->getApplication($id);
        if (isset($application) && $application['emissions']) {
            $form->get('Fields')
                ->get('MeetsEuro6')
                ->setValue('Yes');
        }

        $data = $this->params()->fromPost();
        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                $update['emissions'] = ($data['Fields']['MeetsEuro6'] === 'Yes') ? 1 : 0;
                $command = UpdateEcmtEmissions::create(['id' => $id, 'emissions' => $update['emissions']]);

                $response = $this->handleCommand($command);
                $insert = $response->getResult();

                $this->handleRedirect($data, EcmtSection::ROUTE_ECMT_CABOTAGE);
            } else {
                //Custom Error Message
                $form->get('Fields')->get('MeetsEuro6')->setMessages(['error.messages.checkbox.euro6']);
            }
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function cabotageAction()
    {
        $form = $this->getForm('CabotageForm');

        // read data
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);
        if (isset($application) && $application['cabotage']) {
            $form->get('Fields')->get('WontCabotage')->setValue('Yes');
        }

        //  saving
        $data = $this->params()->fromPost();
        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                $cabotage = ($data['Fields']['WontCabotage'] === 'Yes') ? 1 : 0;
                $command = UpdateEcmtCabotage::create([ 'id' => $id, 'cabotage' => $cabotage]);

                $response = $this->handleCommand($command);
                $insert = $response->getResult();

                $this->handleRedirect($data, EcmtSection::ROUTE_ECMT_COUNTRIES);
            } else {
                //Custom Error Message
                $form->get('Fields')->get('WontCabotage')->setMessages(['error.messages.checkbox.cabotage']);
            }
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function restrictedCountriesAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('RestrictedCountriesForm');

        // Read data
        $application = $this->getApplication($id);

        if (count($application['countrys']) > 0) {
            $form->get('Fields')
                ->get('restrictedCountries')
                ->setValue('1');

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
                if (($data['Fields']['restrictedCountries'] == 1
                    && isset($data['Fields']['restrictedCountriesList']['restrictedCountriesList']))
                    || ($data['Fields']['restrictedCountries'] == 0)
                ) {
                    $countryIds = $data['Fields']['restrictedCountriesList']['restrictedCountriesList'];
                    $command = UpdateEcmtCountries::create(['ecmtApplicationId' => $id, 'countryIds' => $countryIds]);

                    $response = $this->handleCommand($command);
                    $insert = $response->getResult();

                    $this->handleRedirect($data, EcmtSection::ROUTE_ECMT_NO_OF_PERMITS);
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

        // TODO: insert the trips hint into the form
        $trafficArea = $application['licence']['trafficArea'];
        $trafficAreaName = $trafficArea['name'];

        $licenceTrafficArea = $application['licence']['licNo'] . ' (' . $trafficAreaName . ')';
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $tripsHint = $translationHelper->translateReplace('permits.page.trips.form.hint', [$licenceTrafficArea]);

        //Create form from annotations
        $form = $this->getForm('TripsForm');

        $existing['Fields']['tripsAbroad'] = $application['trips'];
        $form->setData($existing);

        $data = $this->params()->fromPost();

        if (!empty($data)) {
            //Validate
            $form->setData($data);

            if ($form->isValid()) {
                $command = UpdateEcmtTrips::create(['id' => $id, 'ecmtTrips' => $data['Fields']['tripsAbroad']]);
                $this->handleCommand($command);
                $this->handleRedirect($data, EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY);
            } else {
                //Custom Error Message
                $form->get('Fields')->get('tripsAbroad')->setMessages(['error.messages.trips']);
            }
        }

        return array('form' => $form, 'ref' => $application['applicationRef'], 'id' => $id, 'trafficAreaId' => $trafficArea['id']);
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

                $this->handleRedirect($data, EcmtSection::ROUTE_ECMT_SECTORS);
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

                    $this->handleRedirect($data, EcmtSection::ROUTE_ECMT_CHECK_ANSWERS);
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('SectorList')
                    ->setMessages(['error.messages.sector.list']);
            }
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    // TODO: remove all session elements and replace with queries
    // TODO: correct form validation so that max value == total vehicle authority (currently hardcoded). See acceptance criteria
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

                $this->handleRedirect($data, EcmtSection::ROUTE_ECMT_TRIPS);
            }
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $totalVehicles = $translationHelper->translateReplace('permits.form.permits-required.hint', [$application['licence']['totAuthVehicles']]);
        $form->get('Fields')->get('permitsRequired')->setOption('hint', $totalVehicles);

        $guidanceMessage = $translationHelper->translateReplace('permits.form.permits-required.fee.guidance', ['£' . $ecmtApplicationFee]);

        return array('form' => $form, 'guidanceMessage' => $guidanceMessage, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    // TODO: remove all session elements and replace with queries
    public function checkAnswersAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        if (!$application['sectionCompletion']['allCompleted']) {
            $this->nextStep(EcmtSection::ROUTE_APPLICATION_OVERVIEW);
        }

        if (!empty($this->params()->fromPost())) {
            $command = UpdateEcmtCheckAnswers::create(['id' => $id]);
            $this->handleCommand($command);
            $this->nextStep(EcmtSection::ROUTE_ECMT_DECLARATION);
        }

        $form = $this->getForm('CheckAnswersForm');


        $answerData = $this->collatePermitQuestions(); //Get all the questions in returned array

        $answerData['licenceAnswer'] = $application['licence']['licNo'] . "\n" . '(' . $application['licence']['trafficArea']['name'] . ')';
        $answerData['meetsEuro6Answer'] = $application['emissions'] == 1 ? 'Yes' : 'No';
        $answerData['cabotageAnswer'] = $application['cabotage'] == 1 ? 'Yes' : 'No';
        $answerData['tripsAnswer'] = $application['trips'];
        $answerData['permitsAnswer'] = $application['permitsRequired'];

        //Restricted Coutries Question
        $answerData['restrictedCountriesAnswer'] = "No";

        if (count($application['countrys']) > 0) {
            $answerData['restrictedCountriesAnswer'] = "Yes\n";
            $count = 1;
            $numOfCountries = count($application['countrys']);

            foreach ($application['countrys'] as $countryDetails) {
                $answerData['restrictedCountriesAnswer'] .= $countryDetails['countryDesc'];

                if (!($count == $numOfCountries)) {
                    $answerData['restrictedCountriesAnswer'] .= ', ';
                }

                $count++;
            }
        }

        //International Journeys Question
        /**
         * @todo ugly - had to do this because this info is currently stored (wrongly) as a number,
         * and a switch statement doesn't do the type check.
         * Can be removed following OLCS-21033
         */
        if ($application['internationalJourneys'] === null) {
            $answerData['percentageAnswer'] = 'Not completed';
        } else {
            $answerData['percentageAnswer'] = $application['internationalJourneys']['description'];
        }

        //Sectors Question
        $answerData['specialistHaulageAnswer'] = 'No';

        if (isset($application['sectors']['description'])) {
            $answerData['specialistHaulageAnswer'] = $application['sectors']['description'];
        }

        return array('sessionData' => $answerData, 'applicationData' => $application, 'form' => $form);
    }

    // TODO: remove all session elements and replace with queries
    public function declarationAction()
    {
        $id = $this->params()->fromRoute('id', -1);


        $form = $this->getForm('DeclarationForm');

        $application = $this->getApplication($id);
        $existing['Fields']['declaration'] = $application['declaration'];
        $form->setData($existing);

        $data = $this->params()->fromPost();

        if (!empty($data)) {
            $form->setData($data);

            if ($form->isValid()) {
                $command = UpdateDeclaration::create(
                    [
                        'id' => $id,
                        'declaration' => $data['Fields']['declaration']
                    ]
                );
                $this->handleCommand($command);

                $this->nextStep(EcmtSection::ROUTE_ECMT_FEE);
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('declaration')
                    ->setMessages(['error.messages.checkbox']);
            }
        }

        return array('form' => $form, 'id' => $id);
    }

    // TODO: remove all session elements and replace with queries
    public function feeAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        if (!empty($this->params()->fromPost())) {
            $command = EcmtSubmitApplication::create(['id' => $id]);
            $this->handleCommand($command);
            $this->nextStep(EcmtSection::ROUTE_ECMT_SUBMITTED);
        }

        $application = $this->getApplication($id);
        $form = $this->getForm('FeesForm');

        // Get Fee Data
        $ecmtPermitFees = $this->getEcmtPermitFees();
        $ecmtApplicationFee =  $ecmtPermitFees['fee'][$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE]['fixedValue'];
        $ecmtApplicationFeeTotal = $ecmtApplicationFee * $application['permitsRequired'];
        $ecmtIssuingFee = $ecmtPermitFees['fee'][$this::ECMT_ISSUING_FEE_PRODUCT_REFENCE]['fixedValue'];

        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('permitsNo', $application['applicationRef']);
        $view->setVariable('applicationDate', $application['createdOn']);
        $view->setVariable('id', $id);
        $view->setVariable('noOfPermits', $application['permitsRequired']);
        $view->setVariable('fee', $ecmtApplicationFee);
        $view->setVariable('totalFee', $ecmtApplicationFeeTotal);
        $view->setVariable('issuingFee', $ecmtIssuingFee);

        return $view;
    }

    public function submittedAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        $application = $this->getApplication($id);

        $view = new ViewModel();
        $view->setVariable('refNumber', $application['applicationRef']);

        return $view;
    }

    public function cancelApplicationAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        $request = $this->getRequest();
        $data = (array)$request->getPost();

        $application = $this->getApplication($id);

        //Create form from annotations
        $form = $this->getForm('CancelApplicationForm');

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);

            if ($form->isValid()) {
                $queryParams = array();
                $queryParams['id'] = $id;

                $command = CancelEcmtPermitApplication::create($queryParams);

                $response = $this->handleCommand($command);
                $insert = $response->getResult();

                $this->nextStep(EcmtSection::ROUTE_ECMT_CANCEL_CONFIRMATION);
            }
        }

        $view = new ViewModel();

        $view->setVariable('form', $form);
        $view->setVariable('id', $id);
        $view->setVariable('ref', $application['applicationRef']);

        return $view;
    }

    public function cancelConfirmationAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        $view = new ViewModel();
        $view->setVariable('id', $id);

        return $view;
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


        // todo: Possibly move this into a session var instead of GET Query Param
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


    public function withdrawApplicationAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        if (!$application['canBeWithdrawn']) {
            $this->redirect()->toRoute('permits');
        }

        $data = $this->params()->fromPost();

        //Create form from annotations
        $form = $this->getForm('WithdrawApplicationForm');

        if (isset($data['Submit'])) {
            //Validate
            $form->setData($data);

            if ($form->isValid()) {
                $command = WithdrawEcmtPermitApplication::create(['id' => $id]);
                $this->handleCommand($command);
                $this->nextStep(EcmtSection::ROUTE_ECMT_WITHDRAW_CONFIRMATION);
            }
        }

        $view = new ViewModel();

        $view->setVariable('id', $id);
        $view->setVariable('form', $form);
        $view->setVariable('ref', $application['applicationRef']);

        return $view;
    }

    public function withdrawConfirmationAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        $view = new ViewModel();

        $view->setVariable('id', $id);
        $view->setVariable('ref', $application['applicationRef']);

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
            $this->redirect()->toRoute('permits');
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

    // TODO: remove this method once all session functionality is removed

    /**
     * Returns a new array with all the question titles
     *
     *
     * @return array
     */
    private function collatePermitQuestions()
    {
        $sessionData = array();

        //SELECTED LICENCE
        $sessionData['licenceQuestion']
          = 'check-answers.page.question.licence';

        //EURO 6 EMISSIONS CONFIRMATION
        $sessionData['meetsEuro6Question']
          = 'check-answers.page.question.euro6';

        //CABOTAGE CONFIRMATION
        $sessionData['cabotageQuestion']
          = 'check-answers.page.question.cabotage';

        //RESTRICTED COUNTRIES
        $sessionData['restrictedCountriesQuestion']
          = 'check-answers.page.question.restricted-countries';

        //NUMBER OF TRIPS PER YEAR
        $sessionData['tripsQuestion']
          = 'check-answers.page.question.trips';

        //'PERCENTAGE' QUESTION
        $sessionData['percentageQuestion']
          = 'check-answers.page.question.internationalJourneys';

        //SECTORS QUESTION
        $sessionData['specialistHaulageQuestion']
          = 'check-answers.page.question.sector';

        //NUMBER OF PERMITS REQUIRED
        $sessionData['permitsQuestion']
          = 'check-answers.page.question.permits-required';

        return $sessionData;
    }

    private function nextStep(string $route)
    {
        $this->redirect()->toRoute('permits/' . $route, [], [], true);
    }

    private function getForm(string $formName): Form
    {
        //Create form from annotations
        return $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm($formName, true, false);
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

    /**
     * Decides the route of the application
     * after a form has been Submitted
     *
     * @param $submittedData - an array of the data submitted by the form
     * @param $nextStep - the EcmtSection:: route to be taken if the form was submitted normally
     */
    private function handleRedirect(array $submittedData, string $nextStep)
    {
        if (array_key_exists('SubmitButton', $submittedData['Submit'])) {
            //Form was submitted normally so continue on chosen path
            return $this->nextStep($nextStep);
        }

        //A button other than the primary submit button was clicked so return to overview
        return $this->nextStep(EcmtSection::ROUTE_APPLICATION_OVERVIEW);
    }
}
