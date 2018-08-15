<?php
namespace Permits\Controller;

use Common\Controller\AbstractOlcsController;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\Form\Form;

use Dvsa\Olcs\Transfer\Command\Permits\UpdateDeclaration;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCheckAnswers;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtLicence;
use Dvsa\Olcs\Transfer\Query\Permits\ConstrainedCountries;
use Dvsa\Olcs\Transfer\Query\Permits\SectorsList;

use Dvsa\Olcs\Transfer\Query\Organisation\EligibleForPermits;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Query\Permits\ById;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitApplication;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermits;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtCountriesList;

use Dvsa\Olcs\Transfer\Command\Permits\CancelEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;

use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCabotage;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtEmissions;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCountries;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtPermitsRequired;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtTrips;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateInternationalJourney;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateSector;
use Dvsa\Olcs\Transfer\Command\Permits\EcmtSubmitApplication;

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

        $licenceList = $this->getRelevantLicences();

        $query = EcmtPermitApplication::create(['order' => 'DESC']);
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

        $introMarkUp['value'] = 'markup-ecmt-permit-guidance-first-time';
        $introMarkUp['switch'] = true;

        if (empty($licenceList)){
            $introMarkUp['value'] = 'markup-ecmt-permit-guidance-no-licence';
            $introMarkUp['switch'] = false;
        }


        $view->setVariable('isEligible', $eligibleForPermits);
        $view->setVariable('introMarkUp', $introMarkUp);
        $view->setVariable('issuedNo', $issuedData['count']);
        $view->setVariable('applicationsNo', $applicationData['count']);
        $view->setVariable('applicationsTable', $applicationsTable);
        $view->setVariable('issuedTable', $issuedTable);

        return $view;
    }

    public function ecmtLicenceAction()
    {
        $id = $this->params()->fromRoute('id', '');
        $application = $this->getApplication($id);

        $form = $this->getEcmtLicenceForm($application['licence']['id']);
        $data = $this->params()->fromPost();
        $application = $this->getApplication($id);

        // Read Data
        if ($application['licence']) {
            // Large amount of formatting due to the way the fields are represented.
            $currentLicence = $application['licence']['id'] . '|' .
                $application['licence']['licNo'] . " " .
                $application['licence']['trafficArea']['name'] . " ";

            $form->get('Fields')->get('EcmtLicence')->setValue($currentLicence);
        }

        if (isset($data['Fields']['Cancel'])) {
            $this->redirect()
                ->toRoute('permits');
        }

        if (isset($data['Fields']['SubmitButton'])) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                $licenceId = explode('|', $data['Fields']['EcmtLicence'])[0];
                $existingApplicationId = $this->params()->fromRoute('id', -1);
                if ($existingApplicationId == -1) {
                    $applicationData['licence'] = $licenceId;
                    $command = CreateEcmtPermitApplication::create($applicationData);
                    $response = $this->handleCommand($command);
                    $insert = $response->getResult();
                } else {
                    // Redirect to confirmation page before clearning answers. Possibly better in Session than as GET Param?
                    $this->redirect()
                        ->toRoute('permits/' . EcmtSection::ROUTE_ECMT_CONFIRM_CHANGE,
                            ['id' => $existingApplicationId],
                            [ 'query' => [
                                'licenceId' => $licenceId
                                ]
                            ]);
                }

                $this->redirect()
                    ->toRoute('permits/' . EcmtSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $insert['id']['ecmtPermitApplication']]);
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('EcmtLicence')
                    ->setMessages(['error.messages.ecmt-licence']);
            }
        }

        return array('form' => $form, 'id' => $id);
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
                $form->get('Fields')->get('MeetsEuro6')->setMessages(['error.messages.checkbox']);
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
                $form->get('Fields')->get('WontCabotage')->setMessages(['error.messages.checkbox']);
            }
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function restrictedCountriesAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('RestrictedCountriesForm');

        /*
        * Get Countries List from Database
        */
        $response = $this->handleQuery(ConstrainedCountries::create([]));
        $restrictedCountryList = $response->getResult();

        /*
        * Make the restricted countries list the value_options of the form
        */
        $restrictedCountryList = $this->getServiceLocator()
            ->get('Helper\Form')
            ->transformListIntoValueOptions($restrictedCountryList, 'description');

        $options = array();
        $options['value_options'] = $restrictedCountryList;
        $form->get('Fields')
            ->get('restrictedCountriesList')
            ->get('restrictedCountriesList')
            ->setOptions($options);

        // Read data
        $application = $this->getApplication($id);

        if (count($application['countrys']) > 0) {
            $form->get('Fields')
                ->get('restrictedCountries')
                ->setValue('1');

            //Format results from DB before setting values on form
            $selectedValues = array();

            foreach ($application['countrys'] as $country) {
                $selectedValues[] = $country['id'] . $this::DEFAULT_SEPARATOR . $country['countryDesc'];
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
                    $countriesList = $data['Fields']['restrictedCountriesList']['restrictedCountriesList'];
                    $countryIds = $this->extractIDFromSessionData($countriesList);
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
            }
        }

        return array('form' => $form, 'ref' => $application['applicationRef'], 'id' => $id, 'trafficAreaId' => $trafficArea['id']);
    }

    public function internationalJourneyAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

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

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function sectorAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        //Create form from annotations
        $form = $this->getForm('SpecialistHaulageForm');

        /*
        * Get Sector List from Database
        */
        $response = $this->handleQuery(SectorsList::create(array()));
        $sectorList = $response->getResult();

        /*
        * Make the sectors list the value_options of the form
        */
        $sectorList = $this->getServiceLocator()
            ->get('Helper\Form')
            ->transformListIntoValueOptions($sectorList, 'description');

        $options = array();
        $options['value_options'] = $sectorList;
        $form->get('Fields')
            ->get('SectorList')
            ->get('SectorList')
            ->setOptions($options);

        // Read data
        $application = $this->getApplication($id);

        if (isset($application)) {
            if (isset($application['sectors'])) {
                $form->get('Fields')->get('SpecialistHaulage')->setValue('1');

                //Format results from DB before setting values on form
                $selectedValue = $application['sectors']['id'] . $this::DEFAULT_SEPARATOR . $application['sectors']['description'];

                $form->get('Fields')
                    ->get('SectorList')
                    ->get('SectorList')
                    ->setValue($selectedValue);
            }
        }

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                //EXTRA VALIDATION
                if (($data['Fields']['SpecialistHaulage'] == 1
                    && isset($data['Fields']['SectorList']['SectorList']))
                    || ($data['Fields']['SpecialistHaulage'] == 0)
                ) {
                    $tmpSectorArray[0] = $data['Fields']['SectorList']['SectorList']; //pass into array in preparation for extractIDFromSessionData()
                    $sectorIDArray = $this->extractIDFromSessionData($tmpSectorArray);

                    $command = UpdateSector::create(['id' => $id, 'sector' => $sectorIDArray[0]]); //$sectorIDArray[0] because should only be 1 entry

                    $response = $this->handleCommand($command);
                    $result = $response->getResult();

                    $this->handleRedirect($data, EcmtSection::ROUTE_ECMT_CHECK_ANSWERS);
                } else {
                    //conditional validation failed, sector list should not be empty
                    $form->get('Fields')
                        ->get('SectorList')
                        ->get('SectorList')
                        ->setMessages(['error.messages.sector.list']);
                }
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('SpecialistHaulage')
                    ->setMessages(['error.messages.sector']);
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

        if (!empty($this->params()->fromPost())) {
            $command = UpdateEcmtCheckAnswers::create(['id' => $id]);
            $this->handleCommand($command);
            $this->nextStep(EcmtSection::ROUTE_ECMT_DECLARATION);
        }

        $form = $this->getForm('CheckAnswersForm');

        $application = $this->getApplication($id);
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
            switch ($application['internationalJourneys']) {
                case 0:
                    $answerData['percentageAnswer'] = 'less.than.60%';
                    break;
                case 1:
                    $answerData['percentageAnswer'] = 'from.60%.to.90%';
                    break;
                case 2:
                    $answerData['percentageAnswer'] = 'more.than.90%';
                    break;
            }
        }

        //Sectors Question
        $answerData['specialistHaulageAnswer'] = 'No';

        if (isset($application['sectors']['description'])) {
            $answerData['specialistHaulageAnswer'] = $application['sectors']['description'];
        }

        return array('sessionData' => $answerData, 'applicationData' => $application, 'form' => $form);
    }

    // TODO: remove all session elements and replace with queries
    public function summaryAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        $session = new Container(self::SESSION_NAMESPACE);
        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('submit', $data)) {
            //Save data to session
            $session->wontCabotage = $data['wontCabotage'];
        }
        /*
         * Collate session data for use in view
         */
        $sessionData = array();
        $sessionData['countriesQuestion'] = 'Are you transporting goods to a
                                        restricted country such as Austria,
                                        Greece, Hungary, Italy or Russia?';

        $sessionData['countries'] = array();

        if ($session->restrictedCountries == 1) {
            foreach ($session->restrictedCountriesList as $country) {
                //add everything right of '|' to the list of countries to get rid of the sector ID
                array_push($sessionData['countries'], substr($country, strpos($country, $this::DEFAULT_SEPARATOR) + 1));
            }
        } else {
            array_push($sessionData['countries'], 'No');
        }

        $sessionData['meetsEuro6Question'] = 'Do your vehicles meet Euro 6 emissions standards?';
        $sessionData['meetsEuro6'] = $session->meetsEuro6 == 1 ? 'Yes' : 'No';

        $sessionData['cabotageQuestion'] = 'Will you be carrying out cabotage?';
        $sessionData['cabotage'] = $session->wontCabotage == 1 ? 'Yes' : 'No';

        return array('sessionData' => $sessionData, 'applicationData' => $application);
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

    /**
     * Used to retrieve the licences for the ecmt-licence page.
     *
     * @return array
     *
     */
    private function getRelevantLicences()
    {
        $organisationId = $this->getCurrentOrganisationId();
        $query = Organisation::create(['id' => $organisationId]);

        $response = $this->handleQuery($query);
        $organisationData = $response->getResult();

        return $organisationData['relevantLicences'];
    }

    private function getEcmtLicenceForm($licenceId = null)
    {
        // TODO: MOVE THIS TO A SERVICE/HELPER
        /*
         * Create form from annotations
         */
        $form = $this->getForm('EcmtLicenceForm');

        /*
         * Get licence to display in question
         */
        $licenceList = $this->getRelevantLicences();

        $value_options = array();
        foreach ($licenceList as $item) {
            $tmp = array();
            $tmp['value'] = $item['id'];
            $tmp['label'] = $item['licNo'] . ' (' . $item['trafficArea'] . ')';

            if($licenceId === $item['id']) {
                $tmp['selected'] = true;
            }

            if($item['licenceType']['id'] === 'ltyp_r') {
                $tmp['attributes'] = [
                    'class' => 'restricted-licence ' . $form->get('Fields')->get('EcmtLicence')->getAttributes()['class']
                ];
                $tmp['label_attributes'] = [
                    'class' => 'restricted-licence-label ' . $form->get('Fields')->get('EcmtLicence')->getLabelAttributes()['class']
                ];
                $value_options[] = $tmp;

                $tmp = array();
                $tmp['value'] = '';
                $tmp['label'] = 'permits.form.ecmt-licence.restricted-licence.hint';
                $tmp['label_attributes'] = [
                    'class' => 'restricted-licence-hint ' . $form->get('Fields')->get('EcmtLicence')->getLabelAttributes()['class']
                ];
                $tmp['attributes'] = [
                    'class' => 'visually-hidden'
                ];
                $value_options[] = $tmp;
            } else {
                $value_options[] = $tmp;
            }
        }

        if (count($value_options) == 0) {
            $form->get('Fields')
                ->get('SubmitButton')
                ->setAttribute('class', 'visually-hidden');

            $form->get('Fields')
                ->get('EcmtLicence')
                ->setOptions(['label' => '']);
        }

        $options = array();
        $options['value_options'] = $value_options;
        $form->get('Fields')
            ->get('EcmtLicence')
            ->setOptions($options);

        return $form;
    }

    // TODO: remove this method once all session functionality is removed
    private function extractIDFromSessionData($sessionData)
    {
        $IDList = array();
        foreach ($sessionData as $entry) {
            //Add everything before the separator to the list (ID is before separator)
            array_push($IDList, substr($entry, 0, strpos($entry, self::DEFAULT_SEPARATOR)));
        }

        return $IDList;
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
        // echo 'test'; die;
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
