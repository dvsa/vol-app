<?php
/**
 *  Registered Company Details Form
 *
 *  @author     S Lizzio <shaun.lizzio@valtech.co.uk>
 *  @package    olcs
 *  @subpackage application
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;

class RegisteredOfficeDetailsForm extends Form
{

 public $countryArray;
 
 public function __construct($name = null)  {
        // we want to ignore the name passed
        parent::__construct('RegisteredOfficeDetailsForm');
        
        $this->countryArray = include (__DIR__ . '/../../../../config/countries.config.php');

        $this->setAttribute('class', 'application-new-form form-horizontal');
        $this->setAttribute('action', '/application/details');

        $this->add(array(
            'name' => 'postcode',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'postcode',
            )
        ));
        $this->add(array(
            'name' => 'findaddressbutton',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-search',
                'id' => 'findaddressbutton',
                'value' => 'Find Address'
            )
        ));
        $this->add(array(
            'name' => 'addressLine1',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'addressLine1',
            )
        ));
        $this->add(array(
            'name' => 'addressLine2',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'addressLine2',
            )
        ));
        $this->add(array(
            'name' => 'addressLine3',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'addressLine3',
            )
        ));
        $this->add(array(
            'name' => 'addressLine4',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'addressLine4',
            )
        ));
        $this->add(array(
            'name' => 'townCity',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'townCity',
            )
        ));
        
        $this->add(array(
            'name' => 'country',
            'type' => 'Zend\Form\Element\Select',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'country'
                )
        ));
        $entityTypesElem = $this->get('country');
        $entityTypeGroup = $this->getCountryList($this->countryArray);
        $entityTypesElem->setValueOptions($this->setSelect($entityTypeGroup, 'Please select'));
        $entityTypesElem->setValue('GB');
    }
    
    
    /*
     * Gets options for a select with an optional label
     */
    public function setSelect($options, $label=null)
    {
        if (!empty($label)) $returnOptions = array('' => $label);
        foreach ($options as $key => $option) {
            $returnOptions[$key] = $option;
        }
        return $returnOptions;
    }
    
    private function getSelectResourceStrings($options)
    {
        $resources = $this->getResourceStrings();
        $resourceHelper = new \Olcs\View\Helper\ResourceHelper($resources);
        foreach($options as $key => $value) {
            $value = str_replace(' ', '-',   strtolower($value));
            $retOptions[$key] = $resourceHelper($value);
        }
        return $retOptions;
    }
    
    private function getResourceStrings() {

        $reader = new \Zend\Config\Reader\Ini();
        $data   = $reader->fromFile(__DIR__ . '/../../../../config/application.ini');
        return $data['section'];

    }
    
    private function getCountryList() {
        $resources = $this->getResourceStrings();
        $resourceHelper = new \Olcs\View\Helper\ResourceHelper($resources);
        
        $countries = $this->countryArray;
        foreach($countries as $ccode => $c_info) {
            $value = str_replace(' ', '-',   strtolower($c_info['label']));
            
            // try and look up
            $resource_value = $resourceHelper($value);
            
            // but if not found, just use array value
            if (empty($resource_value))
            {    
                $retOptions[$ccode] = $c_info['label'];
            } else {
                // use resource value
                $retOptions[$ccode] = $resource_value;
            }
                
        }
        return $retOptions;

    }

    public function isValid() {
        return true;
    }
}

