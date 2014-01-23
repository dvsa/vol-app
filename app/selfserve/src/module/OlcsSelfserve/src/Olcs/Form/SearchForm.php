<?php

namespace Olcs\Form;

use Zend\Form\Form;

class SearchForm extends Form {
    
    public function __construct($name = null)  {
        
        // we want to ignore the name passed
        parent::__construct('basicLookup');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('action', '/search');
        
        $this->add(array(
            'name' => 'licenceNumber',
            'type' => 'Text',
            'required' => true,
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'licenceNumber',
            )
        ));
        $this->add(array(
            'name' => 'operatorName',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'operatorName',
            )
        ));
        $this->add(array(
            'name' => 'postcode',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'postcode',
            )
        ));
        $this->add(array(
            'name' => 'firstName',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'First name',
                'id' => 'firstName',
            )
        ));
        $this->add(array(
            'name' => 'lastName',
            'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'Last name',
                'id' => 'lastName',
            )
        ));
        $this->add(array(     
            'type' => 'Zend\Form\Element\Select',       
            'name' => 'dobDay',
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'dobDay'
            )
        ));
        $this->add(array(     
            'type' => 'Zend\Form\Element\Select',       
            'name' => 'dobMonth',
            'required' => false,
            'attributes' =>  array(
                'class' => 'form-control multiselect dobMonth',
                'id' => 'dobMonth',                
                'options' => array(
                    '' => 'Month', '1' => '1', '2' => '2',
                )
            )
        ));
        $this->add(array(
            'name' => 'dobYear',
            'type' => 'Text',
            'required' => false,
             'attributes' => array(
                'class' => 'form-control',
                'id' => 'dobYear',
                 'placeholder' => 'Year',
            )
        ));
        $this->add(array(
            'name' => 'address',
            'type' => 'Textarea',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'address',
                'rows' => '4'
            )
        ));
        $this->add(array(
            'name' => 'town',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'town',
            )
        ));
        $this->add(array(
            'name' => 'caseNumber',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'caseNumber',
            )
        ));
        $this->add(array(
            'name' => 'transportManagerId',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'transportManagerId',
            )
        ));
        $this->add(array(
            'name' => 'operatorId',
            'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
                'id' => 'operatorId',
            )
        ));
        $this->add(array(
            'name' => 'vehicleRegMark',
            'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
                'id' => 'vehicleRegMark',
            )
        ));
        $this->add(array(
            'name' => 'vehicleRegMark',
            'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
                'id' => 'vehicleRegMark',
            )
        ));
        $this->add(array(
            'name' => 'diskSerialNumber',
            'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
                'id' => 'diskSerialNumber',
            )
        ));
        $this->add(array(
            'name' => 'fabsRef',
            'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
                'id' => 'fabsRef',
            )
        ));
        $this->add(array(
            'name' => 'companyNo',
            'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
                'id' => 'companyNo',
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-success btn-search',
                'id' => 'submitbutton',
                'value' => 'Search'
            )
        ));
        
        $dobDays = $this->get('dobDay');
        $dobDays->setValueOptions($this->getDays());
        $dobMonths = $this->get('dobMonth');
        $dobMonths ->setValueOptions(array(''=>'Month',1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',
                                                                        8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'));
    }
    
    private function getDays() {
        
        $days = array(''=>'Day');
        foreach (range(1, 31) as $number) {
            $days[$number] = $number;
        }
        return $days;
        
    }
    
}
