<?php
namespace Olcs\Form\VCase;

use Zend\Form\Form;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SubmitForm
 *
 * @author valtechuk
 */
class SubmitForm extends Form {
    
    public function __construct($name = null)  {
        
        // we want to ignore the name passed
        parent::__construct('basicLookup');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('action', '#');
        
        $this->add(array(
            'name' => 'caseSummary',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'caseSummary',
            )
        ));

        $this->add(array(
            'name' => 'operatorName',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'operatorName',
            )
        ));
        
        $this->add(array(
            'name' => 'licenceType',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'licenceType',
            )
        ));
        
        $this->add(array(
            'name' => 'mlh',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'mlh',
            )
        ));
        
        
        $this->add(array(
            'name' => 'vAuth',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'vAuth',
            )
        ));
        
        $this->add(array(
            'name' => 'tAuth',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'tAuth',
            )
        ));
        
        $this->add(array(
            'name' => 'startDate',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'startDate',
            )
         ));
        
        $this->add(array(
            'name' => 'entityType',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'entityType',
            )
         ));
        
        $this->add(array(
            'name' => 'licenceNumber',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'licenceNumber',
            )
         ));
        
        
        $this->add(array(
            'name' => 'businessType',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'licenceNumber',
            )
         ));
        
        
        $this->add(array(
            'name' => 'vehicleInPossession',
            'type' => 'Text',
            'attributes' => array(
                'readonly' => 'true',
                'class' => 'form-control',
                'id' => 'vehilceInPossession',
            )
         ));

    }
}

?>
