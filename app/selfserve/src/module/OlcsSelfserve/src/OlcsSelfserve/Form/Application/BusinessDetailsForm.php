<?php
/**
 *  @file /Form/Application/BusinessDetailsForm.php
 * 
 *  Business Type Details Form
 *
 *  @author     S Lizzio <shaun.lizzio@valtech.co.uk>
 *  @package    olcs
 *  @subpackage application
 */

namespace OlcsSelfserve\Form\Application;

use OlcsSelfserve\Form\OlcsForm;

class BusinessDetailsForm extends OlcsForm
{
    
    public function __construct()  {

        parent::__construct('BusinessDetailsForm');

        $this->countryArray = include (__DIR__ . '/../../../../config/countries.config.php');
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'business[entityTypes]',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'business[entityTypes]'
                )
        ));
        
    }
        
    public function isValid() {
        return true;
    }
}

