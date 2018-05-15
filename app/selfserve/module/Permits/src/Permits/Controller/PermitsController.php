<?php

namespace Permits\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Permits\Model\PermitTable;
use Permits\Form\EligibilityForm;
use Permits\Form\Step2Form;

class PermitsController extends AbstractActionController 
{
  private $table;

  public function __construct()
  {
        //$this->table = $table;
  }

  public function indexAction()
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
    return array('form' => $form, 'step' => '1');
  }

  public function step2Action()
  {
    $form = new Step2Form();
    $inputFilter = null;

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
    return array('form' => $form, 'step' => '2', 'inputFilter' => $inputFilter);
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
      $step2Form = new Step2Form();
      $inputFilter = $step2Form->getInputFilter(); //Get validation rules
      $inputFilter->setData($data);

      if($inputFilter->isValid()){
        //valid so save data
      }
    }
    return array('jsonObj' => $jsonObject, 'inputFilter' => $inputFilter, 'step' => '3');
  }

}