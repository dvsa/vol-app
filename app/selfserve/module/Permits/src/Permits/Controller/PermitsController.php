<?php

namespace Permits\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Permits\Form\EligibilityForm;
use Permits\Form\ApplicationForm;
use Permits\Form\TripsForm;
use Permits\Form\SectorsForm;
use Permits\Form\RestrictedCountriesForm;
use Dvsa\Olcs\Transfer\Query\Permits\SectorsList as Sectors;
use Dvsa\Olcs\Transfer\Query\Permits\ConstrainedCountries as Countries;

use Zend\Session\Container; // We need this when using sessions

class PermitsController extends AbstractActionController 
{
    private $sectors;

    const SESSION_NAMESPACE = 'permit_application';

  public function __construct()
  {
  }

    public function indexAction()
    {
        return new ViewModel();
    }

  public function tripsAction()
  {
    $form = new TripsForm();

        return array('form' => $form);
    }

  public function sectorsAction()
  {
    $form = new SectorsForm();

        $data = $this->params()->fromPost();
    if(array_key_exists('submit', $data))
    {
      //Save data to session
      $session = new Container(self::SESSION_NAMESPACE);
      $session->tripsData = $data['numberOfTrips'];
        }

        /*
     * Get Sectors List from Database
     */
    $response = $this->handleQuery(Sectors::create(array()));
    $sectorList = $response->getResult();

    /*
     * Make the Sectors List the value_options of the form
     */
    $options = $form->getDefaultSectorsFieldOptions();
    $options['value_options'] = $this->transformListIntoValueOptions($sectorList);
    $form->get('sectors')->setOptions($options);return array('form' => $form);
    }

  public function restrictedCountriesAction()
  {
    $form = new RestrictedCountriesForm();
    $restrictedCountriesString = '';

    $data = $this->params()->fromPost();

        if(array_key_exists('submit', $data))
    {
      //Save data to session
      $session = new Container(self::SESSION_NAMESPACE);
      $session->sectorsData = $data['sectors'];
    }

    /*
    * Get Sectors List from Database
    */
    $response = $this->handleQuery(Countries::create(array()));
    $restrictedCountryList = $response->getResult();

    /*
    * Make the restricted countries list the value_options of the form
    */
    $options = $form->getDefaultRestrictedCountriesListFieldOptions();
    $restrictedCountryList = $this->transformListIntoValueOptions($restrictedCountryList, 'description');
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

        return array('form' => $form, 'restrictedCountriesString' => $restrictedCountriesString);
    }

    public function summaryAction()
    {
        $session = new Container(self::SESSION_NAMESPACE);

    $data = $this->params()->fromPost();
    if(array_key_exists('submit', $data))
    {
      //Save data to session
      $session->restrictedCountriesData = $data['restrictedCountries'];
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
     foreach($session->sectorsData as $sector)
     {
         //add everything right of '|' to the list of sectors to get rid of the sector ID
         array_push($sessionData['sectors'], substr($sector, strpos($sector, '|') + 1));
     }

     $sessionData['restrictedCountriesQuestion'] = 'Restricted countries';
     $sessionData['restrictedCountries'] = $session->restrictedCountriesData == 1 ? 'Yes' : 'No';

    return array('sessionData' => $sessionData);
    }

    public function declarationAction()
    {
        return new ViewModel();
    }

    public function paymentAction()
    {
        return new ViewModel();
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
        return new ViewModel();
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
            $value_options[$item['id'] . '|' . $item[$displayFieldName]] = $item[$displayFieldName];
        }

        return $value_options;
    }
}
