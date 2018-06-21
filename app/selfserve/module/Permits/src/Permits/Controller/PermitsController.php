<?php

namespace Permits\Controller;

use Permits\Form\PermitApplicationForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Permits\Form\EligibilityForm;
use Permits\Form\ApplicationForm;
use Permits\Form\TripsForm;
use Permits\Form\SectorsForm;
use Dvsa\Olcs\Transfer\Query\Permits\SectorsList as Sectors;
use Dvsa\Olcs\Transfer\Query\Permits\ConstrainedCountries as Countries;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermits;
use Zend\Session\Container;
use Zend\View\View; // We need this when using sessions

class PermitsController extends AbstractActionController
{
    private $sectors;

    const SESSION_NAMESPACE = 'permit_application';
  const DEFAULT_SEPARATOR = '|';

  protected $tableName = 'dashboard-permits';

    public function __construct()
    {
    }

    public function indexAction()
    {

        $query = EcmtPermits::create(array());
        $response = $this->handleQuery($query);
        $dashboardData = $response->getResult();

        $theTable = $this->getServiceLocator()->get('Table')->prepareTable('dashboard-permits', $dashboardData['results']);

        $view = new ViewModel();
        $view->setVariable('permitsNo', $dashboardData['count']);
        $view->setVariable('table', $theTable);

        return $view;
    }

    public function restrictedCountriesAction()
    {

        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Permits\Form\Model\Form\RestrictedCountriesForm');

        $data = $this->params()->fromPost();



        if(array_key_exists('submit', $data))
    {

      //Save data to session
      //$session = new Container(self::SESSION_NAMESPACE);
           // $session->sectorsData = $data['sectors'];
            $form->setData($data);
            if($form->isValid()){

            }
        }

    /*
    * Get Countries List from Database
    */
    $response = $this->handleQuery(Countries::create(array()));
    $restrictedCountryList = $response->getResult();

    /*
    * Make the restricted countries list the value_options of the form
    */
    $restrictedCountryList = $this->transformListIntoValueOptions($restrictedCountryList, 'description');
        $options = array();
    $options['value_options'] = $restrictedCountryList;
    $form->get('restrictedCountriesList')->setOptions($options);

    /*
    * Construct dynamic list of countries
    * for use in titles
    */
    $count = 1;
    foreach($restrictedCountryList as $id => $countryName)
    {
        if($count == count($restrictedCountryList)) //if this country is last
        {
            $restrictedCountriesString = $restrictedCountriesString . '%s ' . $countryName; //%s as placeholder for or/and
        }else{
            $restrictedCountriesString = $restrictedCountriesString . $countryName . ', ';
        }

        $count++;
        }

        return array('form' => $form);
    }

    public function euro6EmissionsAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Permits\Form\Model\Form\Euro6EmissionsForm', false, false);

        $data = $this->params()->fromPost();

        if(array_key_exists('submit', $data))
        {
            //Save data to session
            $session = new Container(self::SESSION_NAMESPACE);
            $session->restrictedCountries = $data['restrictedCountries'];

            if($session->restrictedCountries == 1) //if true
            {
                $session->restrictedCountriesList = $data['restrictedCountriesList'];
            }else{
                $session->restrictedCountriesList = null;
            }

            //create application in db
            if(empty($session->applicationId))
            {
                $applicationData['status'] = 'permit_awaiting';
                $applicationData['paymentStatus'] = 'lfs_ot';
                $command = CreateEcmtPermitApplication::create($applicationData);
                $response = $this->handleCommand($command);
                $insert = $response->getResult();
                $session->applicationId = $insert['id']['ecmtPermitApplication'];
            }

        }

        return array('form' => $form);
    }

    public function cabotageAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Permits\Form\Model\Form\CabotageForm', false, false);

        $data = $this->params()->fromPost();

        if(array_key_exists('submit', $data))
        {
            //Save data to session
            $session = new Container(self::SESSION_NAMESPACE);
            $session->meetsEuro6 = $data['meetsEuro6'];
        }

        return array('form' => $form);
    }

    public function tripsAction()
    {
        $form = new TripsForm();
        return array('form' => $form);
    }

    public function sectorsAction()
    {
        $form = new SectorsForm();
        $session = new Container(self::SESSION_NAMESPACE);
        $data = $this->params()->fromPost();

        if(array_key_exists('submit', $data))
        {
            //Save data to session
            $session->tripsData = $data['numberOfTrips'];
        }else{

        }
        /*
        * Get Sectors List from Database
        */
        $response = $this->handleQuery(Sectors::create(array()));
        $sectorList = $response->getResult();

        //Save count to session for use in summary page (determining if all options were selected).
        $session['totalSectorsCount'] = $sectorList['count'];

        /*
        * Make the Sectors List the value_options of the form
        */
        $options = $form->getDefaultSectorsFieldOptions();
        $options['value_options'] = $this->transformListIntoValueOptions($sectorList);
        $form->get('sectors')->setOptions($options);
        return array('form' => $form);
    }

    public function summaryAction()
    {
        $session = new Container(self::SESSION_NAMESPACE);
        $data = $this->params()->fromPost();

        if(array_key_exists('submit', $data))
        {
            //Save data to session
            $session->willCabotage = $data['willCabotage'];
        }

        /*
         * Collate session data for use in view
         */
        $sessionData = array();
        $sessionData['tripsQuestion'] = 'How many trips will be
                                        made by your company abroad
                                        over the next 12 months?';
     $sessionData['trips'] = $session->tripsData;

     $sessionData['sectorsQuestion'] = 'What type of goods
                                        will you carry over
                                        the next 12 months?';
     $sessionData['sectors'] = array();
     if(count($session->sectorsData) >= $session->totalSectorsCount){
         array_push($sessionData['sectors'], 'All');
     }else {
         foreach ($session->sectorsData as $sector) {
             //add everything right of '|' to the list of sectors to get rid of the sector ID
             array_push($sessionData['sectors'], substr($sector, strpos($sector, $this::DEFAULT_SEPARATOR) + 1));
         }
     }

     $sessionData['restrictedCountriesQuestion'] = 'Restricted countries';
     $sessionData['restrictedCountries'] = $session->restrictedCountriesData == 1 ? 'Yes' : 'No';

    return array('sessionData' => $sessionData);
    }

    public function eligibilityAction()
    {
        $form = new EligibilityForm();
        $request = $this->getRequest();

        if($request->isPost()){
            //If handling returned form (submit clicked)
        }

        return array('form' => $form);
    }

    public function eligibleAction()
    {
        return new ViewModel();
    }

    public function nonEligibleAction()
    {
        return new ViewModel();
    }

    public function applicationAction()
    {
        $form = new ApplicationForm();
        $inputFilter = null;
        $request = $this->getRequest();
        $data['maxApplications'] = 12;

        if ($request->isPost())
        {
            $data = $this->params()->fromPost();
            $jsonObject = json_encode($data);

            $step1Form = new EligibilityForm();
            $inputFilter = $step1Form->getInputFilter();
            $inputFilter->setData($data);

            if ($inputFilter->isValid())
            {
                //valid so save data
            }
        }
        return array('form' => $form, 'data' => $data);
    }

    public function overviewAction()
    {
        return new ViewModel();
    }

    public function declarationAction()
    {
        return new ViewModel();
    }

    public function paymentAction()
    {
        $request = $this->getRequest();
        $data = (array)$request->getPost();
        $session = new Container(self::SESSION_NAMESPACE);

        $view = new ViewModel();
        return $view;
    }

    public function feeAction()
    {
        $request = $this->getRequest();
        $data = (array)$request->getPost();
        $session = new Container(self::SESSION_NAMESPACE);
        if(!empty($data)) {

            $data['ecmtPermitsApplication'] = $session->applicationId;
            $data['status'] = 'permit_awaiting';
            $data['paymentStatus'] = 'lfs_ot';
            $data['intensity'] = '1';

            if($session->restrictedCountries == 1)
            {
                $data['countries'] = $this->extractIDFromSessionData($session->restrictedCountriesList);
            }
            $command = CreateEcmtPermits::create($data);

            $response = $this->handleCommand($command);
            $insert = $response->getResult();
            //TODO undefined index id
            $session->permitsNo = $insert['id']['ecmtPermit'];

            $this->redirect()->toRoute('permits',['action'=>'fee']);
        }
        //TODO missing page title
        $view = new ViewModel();
        $view->setVariable('permitsNo', $session->permitsNo);

        return $view;
    }

    public function step3Action()
    {
        $inputFilter = null;
        $jsonObject = null;
        $request = $this->getRequest();

        if ($request->isPost())
        {
            $data = $this->params()->fromPost();
            $jsonObject = json_encode($data);

            $step2Form = new ApplicationForm();
            $inputFilter = $step2Form->getInputFilter();
            $inputFilter->setData($data);

            if ($inputFilter->isValid())
            {
                //valid so save data
            }
        }
        return array('jsonObj' => $jsonObject, 'inputFilter' => $inputFilter, 'step' => '3');
    }

    /**
     * @return mixed
     */
    public function submittedAction()
    {
        $session = new Container(self::SESSION_NAMESPACE);
        $view = new ViewModel();
        $view->setVariable('refNumber', $session->permitsNo);

        return $view;
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

    private function transformListIntoValueOptions($list = array(), $displayFieldName = 'name')
    {
        if(!is_string($displayFieldName) || !is_array($list)){
            //throw exception?
            return array();
        }

        $value_options = array();

        foreach($list['results'] as $item)
        {
            //add display name to the key so that it can be used after submission
            $value_options[$item['id'] . $this::DEFAULT_SEPARATOR . $item[$displayFieldName]] = $item[$displayFieldName];
        }

        return $value_options;
    }

    private function setFormValueOptionsFromList($form, $formFieldName, $list, $displayFieldName = 'name' )
    {
        $restrictedCountryList = $this->transformListIntoValueOptions($list, $displayFieldName);
        $options = array();
        $options['value_options'] = $restrictedCountryList;
        $form->get($formFieldName)->setOptions($options);

        return $form;
    }
}
