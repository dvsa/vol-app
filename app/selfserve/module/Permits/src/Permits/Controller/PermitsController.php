<?php
namespace Permits\Controller;

use Permits\Form\PermitApplicationForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\Permits\ConstrainedCountries;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermits;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;

use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermits;
use Zend\Session\Container; // We need this when using sessions

class PermitsController extends AbstractActionController
{
    //TODO: Add event for all checks for whether or not $data(from form) is an array
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

    public function ecmtLicenceAction()
    {
        //Create form from annotations
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('EcmtLicenceForm', false, false);

        $data = $this->params()->fromPost();
        if (is_array($data)) {
            if (array_key_exists('Submit', $data)) {
                //Validate
                $form->setData($data);
                if ($form->isValid()) {
                    $session = new Container(self::SESSION_NAMESPACE);
                    $session->meetsEuro6 = $data['Fields']['MeetsEuro6'];

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

        //TEMPORARY
        $applicationFee = "£10.00";
        $issuingFee = "£123.00";

        $view = new ViewModel();
        $view->setVariable('applicationFee', $applicationFee);
        $view->setVariable('issuingFee', $issuingFee);

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