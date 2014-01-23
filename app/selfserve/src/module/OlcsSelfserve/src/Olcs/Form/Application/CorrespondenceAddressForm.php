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

class CorrespondenceAddressForm extends Form
{
    
    public function __construct($name = null)  {
        // we want to ignore the name passed
        parent::__construct('CorrespondenceAddressForm');
        $this->setAttribute('class', 'application-new-form form-horizontal');
        $this->setAttribute('action', '/application/new/licence-details');

        $this->countryArray = include (__DIR__ . '/../../../../config/countries.config.php');
        
        $this->add(array(
            'name' => 'correspondence[contact_type]',
            'type' => 'hidden',
            'attributes' => array(
                'value' => 'correspondence',
                'id' => 'correspondence[contact_type]',
                'class' => '',
            ),
        ));
        
        $this->add(array(
            'name' => 'correspondence[fao]',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'correspondence[fao]',
            )
        ));
        $this->add(array(
            'name' => 'correspondence[postcode]',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'correspondence[postcode]'
            )
        ));

        $this->add(array(
            'name' => 'correspondence[findaddressbutton]',
            'type' => 'button',
            'attributes' => array(
                'class' => 'btn btn-search disabled',
                'id' => 'correspondence[findaddressbutton]',
                'value' => 'Find address'
            )
        ));

        $this->add(array(
            'name' => 'correspondence[addressLine1]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'addressLine1'
            )
        ));

        $this->add(array(
            'name' => 'correspondence[addressLine2]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'addressLine2'
            )
        ));
        
        $this->add(array(
            'name' => 'correspondence[addressLine3]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'addressLine3'
            )
        ));
        
        $this->add(array(
            'name' => 'correspondence[addressLine4]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'correspondence[addressLine4]'
            )
        ));
        
        $this->add(array(
            'name' => 'correspondence[townCity]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'correspondence[townCity]'
            )
        ));
        
        $this->add(array(
            'name' => 'correspondence[country]',
            'type' => 'Zend\Form\Element\Select',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'correspondence[country]'
                )
        ));
        $countryElem = $this->get('correspondence[country]');
        $countryGroup = $this->getCountryList($this->countryArray);
        $countryElem->setValueOptions($this->setSelect($countryGroup, 'Please select'));
        $countryElem->setValue('GB');
        

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'establishmentAddressYN',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'establishmentAddressYN'
                )
        ));
        $establishmentAddressYNElem = $this->get('establishmentAddressYN');
        $yesNoGroup = $this->getSelectResourceStrings(array('yes' => 'select-option-yes', 'no' => 'select-option-no'));
        
        $establishmentAddressYNElem->setValueOptions($this->setSelect($yesNoGroup, 'Please select'));

        $this->add(array(
            'name' => 'correspondence[email]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'correspondence[email]'
            )
        ));
        
        // Establishment address fields
        $this->add(array(
            'name' => 'establishment[contact_type]',
            'type' => 'hidden',
            'attributes' => array(
                'value' => 'establishment',
                'id' => 'establishment[contact_type]',
                'class' => '',
            ),
        ));
        $this->add(array(
            'name' => 'establishment[postcode]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'establishment[postcode]'
            )
        ));
        
        $this->add(array(
            'name' => 'establishment[findaddressbutton]',
            'type' => 'button',
            'attributes' => array(
                'class' => 'btn btn-search disabled',
                'id' => 'establishment[findaddressbutton]',
                'value' => 'Find address'
            )
        ));
        
        $this->add(array(
            'name' => 'establishment[findAddress]',
            'type' => 'button',
            'attributes' => array(
                'class' => 'btn btn-search disabled',
                'id' => 'establishment[findAddress]',
                'value' => 'Find address'
            )
        ));

        $this->add(array(
            'name' => 'establishment[addressLine1]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'establishment[addressLine1]'
            )
        ));

        $this->add(array(
            'name' => 'establishment[addressLine2]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'establishment[addressLine2]'
            )
        ));
        
        $this->add(array(
            'name' => 'establishment[addressLine3]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'establishment[addressLine3]'
            )
        ));
        
        $this->add(array(
            'name' => 'establishment[addressLine4]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'establishment[addressLine4]'
            )
        ));
        
        $this->add(array(
            'name' => 'establishment[townCity]',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'establishment[townCity]'
            )
        ));
        
        $this->add(array(
            'name' => 'establishment[country]',
            'type' => 'Zend\Form\Element\Select',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'establishment[country]'
                )
        ));
        $countryElem = $this->get('establishment[country]');
        $countryGroup = $this->getCountryList($this->countryArray);
        $countryElem->setValueOptions($this->setSelect($countryGroup, 'Please select'));
        $countryElem->setValue('GB');
        
        $this->add(array(
            'name' => 'cancel',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-success btn-cancel',
                'id' => 'cancelbutton',
                'value' => 'Cancel'
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-save pull-right',
                'id' => 'submitbutton',
                'value' => 'Save'
            )
        ));
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

