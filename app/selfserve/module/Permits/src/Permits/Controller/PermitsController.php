<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Permits\Form\PermitApplicationForm;
use Common\Controller\AbstractOlcsController;
use Common\FeatureToggle;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\Permits\ConstrainedCountries;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermits;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;

use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermits;
use Zend\Session\Container; // We need this when using sessions

use Olcs\Controller\Lva\Traits\ExternalControllerTrait;

class PermitsController extends AbstractOlcsController implements ToggleAwareInterface
{
    use ExternalControllerTrait;

    //TODO: Add event for all checks for whether or not $data(from form) is an array
    const SESSION_NAMESPACE = 'permit_application';
    const DEFAULT_SEPARATOR = '|';

    protected $tableName = 'dashboard-permits';

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::SELFSERVE_ECMT
        ],
    ];

    public function indexAction()
    {
        $query = EcmtPermits::create(array());
        $response = $this->handleQuery($query);
        $dashboardData = $response->getResult();

        $theTable = $this->getServiceLocator()->get('Table')->prepareTable($this->tableName, $dashboardData['results']);

        $view = new ViewModel();
        $view->setVariable('permitsNo', $dashboardData['count']);
        $view->setVariable('table', $theTable);

        return $view;
    }


    public function ecmtLicenceAction()
    {
        $form = $this->getEcmtLicenceForm();

        $data = $this->params()->fromPost();
        if (is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    $session = new Container(self::SESSION_NAMESPACE);
                    $session->meetsEuro6 = $data['Fields']['EcmtLicence'];

                    $this->redirect()->toRoute('permits', ['action' => 'application-overview']);
                }
            }
        }

        return array('form' => $form);
    }

    public function applicationOverviewAction()
    {
        $request = $this->getRequest();
        $data = (array)$request->getPost();
        $session = new Container(self::SESSION_NAMESPACE);
        if(is_array($data)) {
            if (!empty($data)) {

            }
        }

        $sections = [array(
            'type' => 'application',
            'variables' => array(
                'enabled' => true,
                'status' => 'COMPLETE',
                'statusColour' => 'green',
                'sectionNumber' => 1,
                'identifier' => 9,
                'name' => 'test',
            )
        )];

        //TEMPORARY
        $applicationFee = "£10.00";
        $issuingFee = "£123.00";

        $view = new ViewModel();
        $view->setVariable('applicationFee', $applicationFee);
        $view->setVariable('issuingFee', $issuingFee);
        $view->setVariable('sections', $sections);

        return $view;
    }

    public function euro6EmissionsAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Euro6EmissionsForm', false, false);

        $data = $this->params()->fromPost();
        if(is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    $session = new Container(self::SESSION_NAMESPACE);
                    $session->meetsEuro6 = $data['Fields']['MeetsEuro6'];

                    $this->redirect()->toRoute('permits', ['action' => 'cabotage']);
                }
            }
        }

        return array('form' => $form);
    }

    public function cabotageAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('CabotageForm', false, false);

        $data = $this->params()->fromPost();
        if(is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    //Save to session
                    $session = new Container(self::SESSION_NAMESPACE);
                    $session->willCabotage = $data['Fields']['WillCabotage'];

                    $this->redirect()->toRoute('permits', ['action' => 'restricted-countries']);
                }
            }
        }

        return array('form' => $form);
    }

    public function restrictedCountriesAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('RestrictedCountriesForm', false, false);

        $data = $this->params()->fromPost();
        if(is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    //EXTRA VALIDATION
                    if (($data['Fields']['restrictedCountries'] == 1
                            && isset($data['Fields']['restrictedCountriesList']['restrictedCountriesList']))
                        || ($data['Fields']['restrictedCountries'] == 0))
                    {

                        //Save data to session
                        $session = new Container(self::SESSION_NAMESPACE);
                        $session->restrictedCountries = $data['Fields']['restrictedCountries'];

                        if ($session->restrictedCountries == 1) //if true
                        {
                            $session->restrictedCountriesList = $data['Fields']['restrictedCountriesList']['restrictedCountriesList'];
                        }
                        else {
                            $session->restrictedCountriesList = null;
                        }

                        //create application in db
                        if (empty($session->applicationId)) {
                            $applicationData['status'] = 'permit_awaiting';
                            $applicationData['paymentStatus'] = 'lfs_ot';
                            $command = CreateEcmtPermitApplication::create($applicationData);
                            $response = $this->handleCommand($command);
                            $insert = $response->getResult();
                            $session->applicationId = $insert['id']['ecmtPermitApplication'];
                        }

                        $this->redirect()->toRoute('permits', ['action' => 'trips']);
                    }
                    else{
                        //conditional validation failed, restricted countries list should not be empty
                        $form->get('Fields')->get('restrictedCountriesList')->get('restrictedCountriesList')->setMessages(['Value is required']);
                    }
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

        return array('form' => $form);
    }

    public function tripsAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
        ->get('Helper\Form')
        ->createForm('TripsForm', false, false);

        $data = $this->params()->fromPost();
        if(is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    //Save to session
                    $session = new Container(self::SESSION_NAMESPACE);
                    $session->willCabotage = $data['Fields']['TripsAbroad'];

                    $this->redirect()->toRoute('permits', ['action' => 'international-journey']);
                }
            }
        }

        return array('form' => $form);
    }

    public function internationalJourneyAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('InternationalJourneyForm', false, false);

        $data = $this->params()->fromPost();
        if (is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    $session = new Container(self::SESSION_NAMESPACE);
                    $session->meetsEuro6 = $data['Fields']['InternationalJourney'];

                    $this->redirect()->toRoute('permits', ['action' => 'sector']);
                }
            }
        }

        return array('form' => $form);
    }

    public function sectorAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('SpecialistHaulageForm', false, false);

        $data = $this->params()->fromPost();
        if(is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    //EXTRA VALIDATION
                    if (($data['Fields']['SpecialistHaulage'] == 1
                            && isset($data['Fields']['SectorList']['SectorList']))
                        || ($data['Fields']['SectorList'] == 0))
                    {

                        //Save data to session
                        $session = new Container(self::SESSION_NAMESPACE);
                        $session->SpecialistHaulage = $data['Fields']['SpecialistHaulage'];

                        if ($session->SpecialistHaulage == 1) //if true
                        {
                            $session->SectorList = $data['Fields']['SectorList']['SectorList'];
                        }
                        else {
                            $session->SectorList = null;
                        }

                        //create application in db
                        if (empty($session->applicationId)) {
                            $applicationData['status'] = 'permit_awaiting';
                            $applicationData['paymentStatus'] = 'lfs_ot';
                            $command = CreateEcmtPermitApplication::create($applicationData);
                            $response = $this->handleCommand($command);
                            $insert = $response->getResult();
                            $session->applicationId = $insert['id']['ecmtPermitApplication'];
                        }

                        $this->redirect()->toRoute('permits', ['action' => 'required-permits']);
                    }
                    else{
                        //conditional validation failed, sector list should not be empty
                        $form->get('Fields')->get('SectorList')->get('SectorList')->setMessages(['Value is required']);
                    }
                }
            }
        }
        /*
        * Get Sector List from Database
        */
        $response = $this->handleQuery(ConstrainedCountries::create(array()));
        $SectorList = $response->getResult();

        /*
        * Make the sectors list the value_options of the form
        */
        $sectorList = $this->getServiceLocator()
            ->get('Helper\Form')->transformListIntoValueOptions($sectorList, 'description');

        $options = array();
        $options['value_options'] = $sectorList;
        $form->get('Fields')->get('SectorList')->get('SectorList')->setOptions($options);

        return array('form' => $form);
    }

    public function checkAnswersAction()
    {
        $session = new Container(self::SESSION_NAMESPACE);
        $data = $this->params()->fromPost();
        if(is_array($data)) {
            if (array_key_exists('submit', $data)) {
                //Save data to session
                $session->willCabotage = $data['willCabotage'];
            }
        }

        $sessionData = $this->collateSessionData();

        return array('sessionData' => $sessionData);
    }

    public function summaryAction()
    {
        $session = new Container(self::SESSION_NAMESPACE);
        $data = $this->params()->fromPost();
        if(is_array($data)) {
            if (array_key_exists('submit', $data)) {
                //Save data to session
                $session->willCabotage = $data['willCabotage'];
            }
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
        $sessionData['cabotage'] = $session->willCabotage == 1 ? 'Yes' : 'No';

        return array('sessionData' => $sessionData);
    }

    public function declarationAction()
    {
        $session = new Container(self::SESSION_NAMESPACE);

        $form = new PermitApplicationForm();
        $form->setData(array(
            'intensity'                 => $session->tripsData,
            'sectors'                   => $this->extractIDFromSessionData($session->sectorsData),
            'restrictedCountries'       => $session->restrictedCountriesData,
            'restrictedCountriesList'   => $this->extractIDFromSessionData($session->restrictedCountriesList)

        ));

        return array('form' => $form);
    }

    public function feeAction()
    {
        $request = $this->getRequest();
        $data = (array)$request->getPost();
        $session = new Container(self::SESSION_NAMESPACE);
        if(is_array($data)) {
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
                //TODO undefined index id
                $session->permitsNo = $insert['id']['ecmtPermit'];

                $this->redirect()->toRoute('permits', ['action' => 'fee']);
            }
        }
        //TODO missing page title
        $view = new ViewModel();
        $view->setVariable('permitsNo', $session->permitsNo);

        return $view;
    }


    public function submittedAction()
    {
        $session = new Container(self::SESSION_NAMESPACE);
        $view = new ViewModel();
        $view->setVariable('refNumber', $session->permitsNo);
        $session->getManager()->getStorage()->clear(self::SESSION_NAMESPACE);
        return $view;
    }

    /**
     * Used to retrieve the licences for the ecmt-licence page.
     *
     * @return mixed
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


    private function extractIDFromSessionData($sessionData){
        $IDList = array();
//TODO check the mess (invalid argument supplied for foreach)
        foreach ($sessionData as $entry){
            //Add everything before the separator to the list (ID is before separator)
            array_push($IDList, substr($entry, 0, strpos($entry, self::DEFAULT_SEPARATOR)));
        }

        return $IDList;
    }

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
        $sessionData['licenceAnswer'] = $session['licence'];

        //EURO 6 EMISSIONS CONFIRMATION
        $sessionData['meetsEuro6Question']
            = 'I confirm that my ECMT permit(s) will only be 
                used by vehicle(s) that are environmentally compliant 
                to Euro 6 emissions standards.';
        $sessionData['meetsEuro6Answer'] = $session['meetsEuro6'];

        //CABOTAGE CONFIRMATION
        $sessionData['cabotageQuestion']
            = 'I confirm that I will not undertake a 
                cabotage journey(s) with an ECMT permit.';
        $sessionData['cabotageAnswer'] = $session['cabotage'];

        //RESTRICTED COUNTRIES
        $sessionData['restrictedCountriesQuestion']
            = 'Do you intend to transport goods to
                Austria, Greece, Hungary, Italy or Russia?';
        $sessionData['restrictedCountriesAnswer'] = $session['restrictedCountries'];

        //NUMBER OF TRIPS PER YEAR
        $sessionData['tripsQuestion']
            = 'How many international trips were carried out over the past 12 months?';
        $sessionData['tripsAnswer'] = $session['trips'];

        //'PERCENTAGE' QUESTION
        $sessionData['percentageQuestion']
            = 'What percentage of your business 
                is related to international journeys over the past 12 months?';
        $sessionData['percentageAnswer'] = $session['percentage'];

        //SECTORS QUESTION
        $sessionData['specialistHaulageQuestion']
            = 'Do you specialise in carrying goods for one specific sector?';
        $sessionData['specialistHaulageAnswer'] = $session['specialistHaulage'];

        //NUMBER OF PERMITS REQUIRED
        $sessionData['permitsQuestion']
            = 'How many permits does your business require?';
        $sessionData['permitsAnswer'] = $session['permits'];

        return $sessionData;
    }

}