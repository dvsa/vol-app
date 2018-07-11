<?php

namespace Permits\Controller;

use Permits\Form\PermitApplicationForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\Permits\ConstrainedCountries;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermits;
use Zend\Session\Container;
use Zend\View\View; // We need this when using sessions

class PermitsController extends AbstractActionController
{
    private $sectors;

    const SESSION_NAMESPACE = 'permit_application';
  const DEFAULT_SEPARATOR = '|';

  protected $tableName = 'dashboard-permits';


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

    public function applicationOverviewAction()
    {
        $request = $this->getRequest();
        $data = (array)$request->getPost();
        $session = new Container(self::SESSION_NAMESPACE);
        if(is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    $session = new Container(self::SESSION_NAMESPACE);
                    $session->meetsEuro6 = $data['Fields']['MeetsEuro6'];

                    $this->redirect()->toRoute('permits', ['action' => 'euro6Emissions']);
                }
            }
        }

        $view = new ViewModel();
        $view->setVariable('applicationFee', $applicationFee);
        $view->setVariable('issuingFee', $issuingFee);

        return $view;
    }

    public function restrictedCountriesAction()
    {

        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('RestrictedCountriesForm');

        $data = $this->params()->fromPost();
        if(is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    //EXTRA VALIDATION
                    if(($data['Fields']['restrictedCountries'] == 1
                            && isset($data['Fields']['restrictedCountriesList']['restrictedCountriesList']))
                        || ($data['Fields']['restrictedCountries'] == 0)) {

                        //Save data to session
                        $session = new Container(self::SESSION_NAMESPACE);
                        $session->restrictedCountries = $data['Fields']['restrictedCountries'];

                        if ($session->restrictedCountries == 1) //if true
                        {
                            $session->restrictedCountriesList = $data['Fields']['restrictedCountriesList']['restrictedCountriesList'];
                        } else {
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

                        $this->redirect()->toRoute('permits', ['action' => 'euro6Emissions']);
                    }else{
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
        $session->getManager()->getStorage()->clear(self::SESSION_NAMESPACE);
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
}
