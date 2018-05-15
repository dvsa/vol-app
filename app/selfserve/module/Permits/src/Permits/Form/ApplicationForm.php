<?php
namespace Permits\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class ApplicationForm extends Form {
  
  private $inputFilter;
  
  public function __construct($name = null)
  {
    //we want to ignore the name passed
    parent::__construct();
    
    $this->inputFilter = null;
    
    $this->add(array(
      'name' => 'id',
      'type' => 'Hidden',
    ));

    $this->add(array(
      'type' => 'MultiCheckBox',
      'name' => 'deliveryCountries',
      'options' => array(
        'label' => '',
        'value_options' => array(
          '0' => 'Austria',
          '1' => 'Greece',
          '2' => 'Hungary',
          '3' => 'Italy',
          '4' => 'Portugal',
          '5' => 'Russia',
          '6' => 'Spain',
        ),
      )
    ));

    $this->add(array(
      'type' => 'Text',
      'name' => 'sector',
      'options' => array(
        'label' => 'What type of goods will you be carrying?',
      ),
    ));

    $this->add(array(
      'name' => 'numberOfTrips',
      'type' => 'Text',
      'options' => array(
        'label' => 'How many trips will you be making?',
      ),
    ));

    $this->add(array(
      'name' => 'submit',
      'type' => 'Submit',
      'attributes' => array(
        'value' => 'Save and continue',
        'id' => 'submitbutton',
        'class' => 'action--primary large',
      ),
    ));
  }
  
  public function getInputFilter()
  {
    if($this->inputFilter == null){ 
      $this->inputFilter = new InputFilter();
      $this->inputFilter->add([
        'name'     => 'MultiCheckBox',
        'required' => true,
        'filters'  => [],               
        'validators' => [
          [
            'name' => 'Regex',
            'options' => [
              'pattern' => '/[0|1|2|3|4|5|6]/'
            ]
          ],     
        ]
      ]);
      $this->inputFilter->add([
        'name'     => 'sector',
        'required' => true,
        'filters'  => [],               
        'validators' => [
          [
            'name' => 'StringLength',
            'options' => [
              'min' => 1,
              'max' => 23
            ]
          ],     
        ]
      ]);
      $this->inputFilter->add([
        'name'     => 'numberOfTrips',
        'required' => true,
        'filters'  => [],               
        'validators' => [
         [
          'name' => 'StringLength',
          'options' => [
            'min' => 1,
            'max' => 23
          ]
        ],     
      ]
    ]);
    }
    
    return $this->inputFilter;
  }
}



?>
