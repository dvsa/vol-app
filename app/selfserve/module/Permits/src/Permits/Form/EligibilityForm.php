<?php
namespace Permits\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class EligibilityForm extends Form {
  
  private $inputFilter;
  private $numOfCountries;
  
  public function __construct($name = null, $numOfCountries = 1)
  {
    //we want to ignore the name passed
    parent::__construct('permit');
    
    $this->inputFilter = null;
    $this->numOfCountries = $numOfCountries;

    $this->add(array(
      'name' => 'id',
      'type' => 'Hidden',
    ));

    $this->add(array(
      'type' => 'Radio',
      'name' => 'willCabotage',
      'options' => array(
        'label' => '',
        'value_options' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
      ),
    ));

    $this->add(array(
      'type' => 'Radio',
      'name' => 'meetsEuro6',
      'options' => array(
        'label' => '',
        'value_options' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
      ),
    ));

    $this->add(array(
      'name' => 'country',
      'type' => 'Collection',
      'options' => array(
        'label' => '',
        'count' => $numOfCountries,
        'should_create_template' => true,
        'target_element' => array(
          'type' => 'Text',
          'options' => array(
            'label' => 'Country',
          ),
        ),
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
        'name'     => 'willCabotage',
        'required' => true,
        'filters'  => [],               
        'validators' => [
          [
            'name' => 'Regex',
            'options' => [
              'pattern' => '/[1|0]/'
            ]
          ],     
        ]
      ]);
      $this->inputFilter->add([
        'name'     => 'meetsEuro6',
        'required' => true,
        'filters'  => [],               
        'validators' => [
          [
            'name' => 'Regex',
            'options' => [
              'pattern' => '/[1|0]/'
            ] 
          ],     
        ]
      ]);
      $this->inputFilter->add([
        'name'     => 'country',
        'type'     => 'Zend\InputFilter\ArrayInput',
        'required' => true,
        'filters'  => [],
        'options'  => array(
          'label'          => 'Product features',
          'count'          => $this->numOfCountries,
                                   // 'should_create_template' => true,
                                    //'allow_add'      => true,
          'target_element' => array(
            'type'    => 'Text',
            'validators' => [
              [
                'name' => 'StringLength',
                'options' => [
                  'min' => 1,
                  'max' => 23
                ]
              ],
            ]
          ),
        ),
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