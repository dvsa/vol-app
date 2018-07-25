<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\AbstractOlcsController;
use Common\FeatureToggle;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\Permits\ConstrainedCountries;
use Dvsa\Olcs\Transfer\Query\Organisation\EligibleForPermits;
use Dvsa\Olcs\Transfer\Query\Permits\SectorsList;

use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermits;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtEmissions;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCabotage;
use Zend\Mvc\MvcEvent;
use Zend\Http\Header\Referer as HttpReferer;
use Zend\Http\PhpEnvironment\Request as HttpRequest;

use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitApplication;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermits;
use Dvsa\Olcs\Transfer\Query\Permits\ById;
use Zend\Session\Container; // We need this when using sessions

use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\View\Helper\EcmtSection;


class PermitsController extends AbstractOlcsController implements ToggleAwareInterface
{
    use ExternalControllerTrait;

    //TODO: Add event for all checks for whether or not $data(from form) is an array
    const SESSION_NAMESPACE = 'permit_application';
    const DEFAULT_SEPARATOR = '|';

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
        $view->setVariable('isEligible', $eligibleForPermits);

        if (!$eligibleForPermits) {
            if (!$this->referredFromGovUkPermits($this->getEvent())) {
                return $this->notFoundAction();
            }

            return $view;
        }

        $query = EcmtPermitApplication::create(array());
        $response = $this->handleQuery($query);
        $applicationData = $response->getResult();

        $query = EcmtPermits::create(array());
        $response = $this->handleQuery($query);
        $issuedData = $response->getResult();

        $applicationsTable = $this->getServiceLocator()->get('Table')->prepareTable($this->applicationsTableName, $applicationData['results']);
        $issuedTable = $this->getServiceLocator()->get('Table')->prepareTable($this->issuedTableName, $issuedData['results']);

        $view->setVariable('issuedNo', $issuedData['count']);
        $view->setVariable('applicationsNo', $applicationData['count']);
        $view->setVariable('applicationsTable', $applicationsTable);
        $view->setVariable('issuedTable', $issuedTable);

        return $view;
    }

    public function ecmtLicenceAction()
    {

        $id = $this->params()->fromRoute('id', '');

        $form = $this->getEcmtLicenceForm();
        $data = $this->params()->fromPost();
        if ($data && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {

                $applicationData['status'] = 'permit_awaiting';
                $applicationData['paymentStatus'] = 'lfs_ot';
                $applicationData['permitType'] = 'permit_ecmt';
                $applicationData['licence'] = explode('|',$data['Fields']['EcmtLicence'])[0];

                //TODO additional validation required: if total of possible permit applications has been reached,
                // the user should not be able to create another application.

                $command = CreateEcmtPermitApplication::create($applicationData);
                $response = $this->handleCommand($command);
                $insert = $response->getResult();
                $this->redirect()->toRoute('permits/' . EcmtSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $insert['id']['ecmtPermitApplication']]);
            }
        }
        return array('form' => $form, 'id' => $id);
    }

    public function applicationOverviewAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);
        $application['check_answers'] = null;
        $application['declaration'] = null;
        $application['ecmt_licence'] = 1;

        $applicationRef = $application['licence']['licNo'] . ' / ' . $application['id'];

        $applicationFee = "£10.00";
        $issuingFee = "£123.00";

        $view = new ViewModel();
        $view->setVariable('ref', $applicationRef);
        $view->setVariable('id', $id);
        $view->setVariable('applicationFee', $applicationFee);
        $view->setVariable('issuingFee', $issuingFee);
        $view->setVariable('application', $application);

        return $view;
    }

    public function euro6EmissionsAction()
    {

        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Euro6EmissionsForm', false, false);

        // read data
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);
        if (isset($application) && $application['emissions']) {
            $form->get('Fields')->get('MeetsEuro6')->setValue('Yes');
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

                $this->nextStep(EcmtSection::ROUTE_ECMT_CABOTAGE);
            }
        }

        return array('form' => $form, 'id' => $id);
    }

    public function cabotageAction()
    {

        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('CabotageForm', false, false);

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
                $command = UpdateEcmtCabotage::create(['id' => $id, 'cabotage' => $cabotage]);

                $response = $this->handleCommand($command);
                $insert = $response->getResult();

                $this->nextStep(EcmtSection::ROUTE_ECMT_COUNTRIES);
            }
        }

        return array('form' => $form, 'id' => $id);
    }

    public function restrictedCountriesAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('RestrictedCountriesForm', false, false);

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {

            //Validate
            $form->setData($data);
            if ($form->isValid()) {

                //EXTRA VALIDATION
                if (($data['Fields']['restrictedCountries'] == 1
                        && isset($data['Fields']['restrictedCountriesList']['restrictedCountriesList']))
                    || ($data['Fields']['restrictedCountries'] == 0))
                {
                    $this->nextStep(EcmtSection::ROUTE_ECMT_NO_OF_PERMITS);
                }
                else{
                    //conditional validation failed, restricted countries list should not be empty
                    $form->get('Fields')->get('restrictedCountriesList')->get('restrictedCountriesList')->setMessages(['error.messages.restricted.countries']);
                }
            }
        }

        /*
        * Get Countries List from Database
        */
        $response = $this->handleQuery(ConstrainedCountries::create(array()));
        $restrictedCountryList = $response->getResult();

        /*
        * Make the restricted countries list the value_options of the form
        */
        $restrictedCountryList = $this->getServiceLocator()
            ->get('Helper\Form')->transformListIntoValueOptions($restrictedCountryList, 'description');

        $options = array();
        $options['value_options'] = $restrictedCountryList;
        $form->get('Fields')->get('restrictedCountriesList')->get('restrictedCountriesList')->setOptions($options);

        return array('form' => $form, 'id' => $id);
    }

    public function tripsAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);
        $applicationRef = $application['licence']['licNo'] . ' / ' . $application['id'];

        //TODO insert the trips hint into the form
        $licenceTrafficArea = $application['licence']['licNo'] . ' (' . $application['licence']['trafficArea']['name'] . ')';
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $tripsHint = $translationHelper->translateReplace('permits.page.trips.form.hint', [$licenceTrafficArea]);

        //Create form from annotations
        $form = $this->getServiceLocator()
        ->get('Helper\Form')
        ->createForm('TripsForm', false, false);

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                $this->nextStep(EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY);
            }
        }

        return array('form' => $form, 'ref' => $applicationRef, 'id' => $id);
    }

    public function internationalJourneyAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('InternationalJourneyForm', false, false);

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                $this->nextStep(EcmtSection::ROUTE_ECMT_SECTORS);
            }
        }

        return array('form' => $form, 'id' => $id);
    }

    public function sectorAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('SpecialistHaulageForm', false, false);
        /*
        * Get Sector List from Database
        */
        $response = $this->handleQuery(SectorsList::create(array()));
        $sectorList = $response->getResult();

        /*
        * Make the sectors list the value_options of the form
        */
        $sectorList = $this->getServiceLocator()
          ->get('Helper\Form')->transformListIntoValueOptions($sectorList, 'description');

        $options = array();
        $options['value_options'] = $sectorList;
        $form->get('Fields')->get('SectorList')->get('SectorList')->setOptions($options);

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                //EXTRA VALIDATION
                if (($data['Fields']['SpecialistHaulage'] == 1
                        && isset($data['Fields']['SectorList']['SectorList']))
                    || ($data['Fields']['SectorList'] == 0))
                {
                    $this->nextStep(EcmtSection::ROUTE_ECMT_CHECK_ANSWERS);
                }
                else{
                    //conditional validation failed, sector list should not be empty
                    $form->get('Fields')->get('SectorList')->get('SectorList')->setMessages('error.messages.sector');
                }
            }
        }

        return array('form' => $form, 'id' => $id);
    }


    //TODO remove all session elements and replace with queries

    public function permitsRequiredAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('PermitsRequiredForm', false, false);

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                //Save to session
                $session = new Container(self::SESSION_NAMESPACE);
                $session->PermitsRequired = $data['Fields']['PermitsRequired'];
                $this->nextStep(EcmtSection::ROUTE_ECMT_TRIPS);
            }
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $totalVehicles = $translationHelper->translateReplace('permits.page.permits.required.info', [$application['licence']['totAuthVehicles']]);

        return array('form' => $form, 'totalVehicles' => $totalVehicles, 'id' => $id);
    }


    //TODO remove all session elements and replace with queries
    public function checkAnswersAction()
    {

        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);
        $applicationRef = $application['licence']['licNo'] . ' / ' . $application['id'];

        $session = new Container(self::SESSION_NAMESPACE);
        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('submit', $data)) {
            //Save data to session
            $session->wontCabotage = $data['wontCabotage'];
        }

        $sessionData = $this->collateSessionData();

        return array('sessionData' => $sessionData, 'applicationData' => $application, 'id' => $id);
    }

    //TODO remove all session elements and replace with queries

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
        if($session->restrictedCountries == 1)
        {
            foreach ($session->restrictedCountriesList as $country) {
                //add everything right of '|' to the list of countries to get rid of the sector ID
                array_push($sessionData['countries'], substr($country, strpos($country, $this::DEFAULT_SEPARATOR) + 1));
            }
        }else{
            array_push($sessionData['countries'], 'No');
        }

        $sessionData['meetsEuro6Question'] = 'Do your vehicles meet Euro 6 emissions standards?';
        $sessionData['meetsEuro6'] = $session->meetsEuro6 == 1 ? 'Yes' : 'No';

        $sessionData['cabotageQuestion'] = 'Will you be carrying out cabotage?';
        $sessionData['cabotage'] = $session->wontCabotage == 1 ? 'Yes' : 'No';

        return array('sessionData' => $sessionData, 'applicationData' => $application);
    }

    //TODO remove all session elements and replace with queries
    public function declarationAction()
    {

        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getServiceLocator()
          ->get('Helper\Form')
          ->createForm('DeclarationForm', false, false);

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            if ($form->isValid()) {
                //Save to session
                $session = new Container(self::SESSION_NAMESPACE);
                $session->Declaration = $data['Fields']['Declaration'];

                $this->nextStep(EcmtSection::ROUTE_ECMT_FEE);
            }
        }

        return array('form' => $form, 'id' => $id);
    }

    //TODO remove all session elements and replace with queries

    public function feeAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);
        $applicationRef = $application['licence']['licNo'] . ' / ' . $application['id'];

        $request = $this->getRequest();
        $data = (array)$request->getPost();
        $session = new Container(self::SESSION_NAMESPACE);

        if (!empty($data)) {
            $data['ecmtPermitsApplication'] = $session->applicationId;
            $data['status'] = 'permit_awaiting';
            $data['paymentStatus'] = 'lfs_ot';
            $data['intensity'] = '1';

            if ($session->restrictedCountries == 1) {
                $data['countries'] = $this->extractIDFromSessionData($session->restrictedCountriesList);
            }
            $command = CreateEcmtPermits::create($data);

            $response = $this->handleCommand($command);
            $insert = $response->getResult();
            $session->permitsNo = $insert['id']['ecmtPermit'];
            $this->nextStep(EcmtSection::ROUTE_ECMT_SUBMITTED);
        }

        $view = new ViewModel();
        $view->setVariable('permitsNo', $applicationRef);
        $view->setVariable('id', $id);

        return $view;
    }


    public function submittedAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);
        $applicationRef = $application['licence']['licNo'] . ' / ' . $application['id'];
        $view = new ViewModel();
        $view->setVariable('refNumber', $applicationRef);
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

    /**
     * Modified version of the method in FormHelperServices
     * that is used by the restricted countries view.
     *
     *
     * @param array $list
     * @param string $displayFieldName
     * @param string $separator
     * @return array
     */
    private function transformListIntoValueOptions($list = array(), $displayMembers = array('name'), $separator = '|')
    {
        //TODO: MOVE THIS INTO FormHelperService AND REPLACE OLD VERSION
        if(!is_string($displayMembers[0]) || !is_array($list)){
            //throw exception?
            return array();
        }

        $value_options = array();
        foreach($list as $item)
        {
            //Concatenate display values (incase there is more than one field to be used)
            $displayValue = "";
            foreach($displayMembers as $displayKey)
            {
                $displayValue = $displayValue . $item[$displayKey] . " ";
            }

            //add display name to the key so that it can be used after submission
            $value_options[$item['id'] . $separator . $displayValue] = $displayValue;
        }

        return $value_options;
    }

    private function getEcmtLicenceForm()
    {
        //TODO: MOVE THIS TO A SERVICE/HELPER
        /*
         * Create form from annotations
         */
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('EcmtLicenceForm', false, false);

        /*
         * Get licence to display in question
         */
        $licenceList = $this->getRelevantLicences();
        $value_options = $this->transformListIntoValueOptions($licenceList, array('licNo', 'trafficArea'));

        /*
         * Add brackets
         */
        foreach($value_options as $key => $value)
        {
            $spacePosition = strpos($value, ' '); //find position of first space
            $newValue = substr_replace($value, ' (', $spacePosition, 1); //add bracket after first space

            $newValue = trim($newValue) . ')';//add bracket to end

            $value_options[$key] = $newValue;//set current value option to reformatted value
        }

        /*
         * Set 'licences to display' as the value_options of the field
         */
        $options = array();
        $options['value_options'] = $value_options;
        $form->get('Fields')->get('EcmtLicence')->setOptions($options);

        return $form;
    }

    //TODO remove this method once all session functionality is removed

    private function extractIDFromSessionData($sessionData){
        $IDList = array();
        foreach ($sessionData as $entry){
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
        //check whether user is allowed to access permits
        return true;
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

    //TODO remove this method once all session functionality is removed

    /**
     * Returns a new array with all the user's answers (taken from the session)
     * and associated question titles (as per the check-answers/summary page).
     *
     *
     * @return array
     */
    private function collateSessionData()
    {
        $session = new Container(self::SESSION_NAMESPACE);
        $sessionData = array();

        //SELECTED LICENCE
        $sessionData['licenceQuestion']
          = 'Licence selected';
        $sessionData['licenceAnswer'] = $session->licence;

        //EURO 6 EMISSIONS CONFIRMATION
        $sessionData['meetsEuro6Question']
          = 'I confirm that my ECMT permit(s) will only be 
                used by vehicle(s) that are environmentally compliant 
                to Euro 6 emissions standards.';
        $sessionData['meetsEuro6Answer'] = $session->meetsEuro6  == 1 ? 'Yes' : 'No';

        //CABOTAGE CONFIRMATION
        $sessionData['cabotageQuestion']
          = 'I confirm that I will not undertake a 
                cabotage journey(s) with an ECMT permit.';
        $sessionData['cabotageAnswer'] = $session->wontCabotage  > 1 ? 'Yes' : 'No';

        //RESTRICTED COUNTRIES
        $sessionData['restrictedCountriesQuestion']
          = 'Do you intend to transport goods to
                Austria, Greece, Hungary, Italy or Russia?';
        if($session->restrictedCountries  == 1)
        {
            $sessionData['restrictedCountriesAnswer'] = [];
            foreach ($session->restrictedCountriesList as $country)
            {
                //add everything right of '|' to the list of countries to get rid of the sector ID
                array_push($sessionData['restrictedCountriesAnswer'], substr($country, strpos($country, $this::DEFAULT_SEPARATOR) + 1));
            }
        }else{
            $sessionData['restrictedCountriesAnswer'] = 'No';
        }

        //NUMBER OF TRIPS PER YEAR
        $sessionData['tripsQuestion']
          = 'How many international trips were carried out over the past 12 months?';
        $sessionData['tripsAnswer'] = $session->trips;

        //'PERCENTAGE' QUESTION
        $sessionData['percentageQuestion']
          = 'What percentage of your business 
                is related to international journeys over the past 12 months?';
        switch ($session->internationalJourneyPercentage) {
            case 0:
                $sessionData['percentageAnswer'] = 'Less than 60%';
                break;
            case 1:
                $sessionData['percentageAnswer'] = 'From 60% to 90%';
                break;
            case 2:
                $sessionData['percentageAnswer'] = 'More than 90%';
                break;
        }

        //SECTORS QUESTION
        $sessionData['specialistHaulageQuestion']
          = 'Do you specialise in carrying goods for one specific sector?';
        if($session->specialistHaulage  == 1)
        {
            $sessionData['specialistHaulageAnswer'] = substr($session->sectorList, strpos($session->sectorList, $this::DEFAULT_SEPARATOR) + 1);
        }else {
            $sessionData['specialistHaulageAnswer'] = 'No';
        }

        //NUMBER OF PERMITS REQUIRED
        $sessionData['permitsQuestion']
          = 'How many permits does your business require?';
        $sessionData['permitsAnswer'] = $session->permitsRequired;

        return $sessionData;
    }

    private function nextStep(string $route)
    {
        $this->redirect()->toRoute('permits/' . $route, [], [], true);
    }

    /**
     * Returns an application entry by id
     *
     * @param $id application id
     * @return array
     */

    private function getApplication($id)
    {
        $query = ById::create(['id'=>$id]);
        $response = $this->handleQuery($query);
        return $response->getResult();
    }

    /**
     * Returns an array for the update command
     *
     * @param $id application id
     * @param $data array
     * @return array
     */
    private function generateApplicationData($id, $data)
    {
        $application = $id;
        $key = key($data);
        $value = $data[$key];
        $applicationData = [
          'id' => $id,
          $key => $value,
          'status' => $application['status']['id'],
          'paymentStatus' => $application['paymentStatus']['id'],
          'permitType' => $application['permitType']['id'],
          'licence' => $application['licence']['id']
        ];
        return $applicationData;
    }
}