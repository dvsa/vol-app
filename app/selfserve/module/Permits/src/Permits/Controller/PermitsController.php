<?php

namespace Permits\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Permits\Form\EligibilityForm;
use Permits\Form\ApplicationForm;
use Permits\Form\TripsForm;
use Permits\Form\SectorsForm;
use Permits\Form\RestrictedCountriesForm;
use Dvsa\Olcs\Transfer\Query\Permits\SectorsList;

class PermitsController extends AbstractActionController 
{
  private $sectors;

  public function __construct()
  {
        $this->sectors = SectorsList::class;
  }

  public function indexAction()
  {
    return new ViewModel();
  }

  public function tripsAction()
  {
    $form = new TripsForm();
    $request = $this->getRequest();

    if($request->isPost())
    {
      $data = $this->params()->fromPost();
      $inputFilter = $form->getInputFilter();
      $inputFilter->setData($data);
    }

    return array('form' => $form, 'data' => $data);
  }

  public function sectorsAction()
  {

      $response = $this->handleQuery($this->sectors::create(array()));
      $formData = $response->getResult();

echo '<pre>';var_dump(($formData['results']));die();

    $form = new SectorsForm();
    $request = $this->getRequest();

    if($request->isPost())
    {
        $data = $this->params()->fromPost();
        $inputFilter = $form->getInputFilter();
        $inputFilter->setData($data);
    }

    return array('form' => $form, 'data' => $data);
  }

  public function restrictedCountriesAction()
  {
    $form = new RestrictedCountriesForm();
    $request = $this->getRequest();

    if($request->isPost())
    {
        $data = $this->params()->fromPost();
        $inputFilter = $form->getInputFilter();
        $inputFilter->setData($data);
    }

    return array('form' => $form, 'data' => $data);
  }

  public function summaryAction()
  {
    return new ViewModel();
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
    $data['maxApplications'] = 12;

    $request = $this->getRequest();
    if($request->isPost()) {
      //If handling returned form (submit clicked)
      $data = $this->params()->fromPost(); //get data from POST
      $jsonObject = json_encode($data); //convert data to JSON

      //START VALIDATION
      $step1Form = new EligibilityForm();
      $inputFilter = $step1Form->getInputFilter(); //Get validation rules
      $inputFilter->setData($data);

      if($inputFilter->isValid()){
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
    if($request->isPost()){
      //If handling returned form (submit clicked)
      $data = $this->params()->fromPost(); //get data from POST
      $jsonObject = json_encode($data); //convert data to JSON
      
      //START VALIDATION
      $step2Form = new ApplicationForm();
      $inputFilter = $step2Form->getInputFilter(); //Get validation rules
      $inputFilter->setData($data);

      if($inputFilter->isValid()){
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

}