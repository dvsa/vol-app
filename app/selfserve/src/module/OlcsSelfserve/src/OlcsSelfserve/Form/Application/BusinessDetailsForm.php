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
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'business[entityTypes]',
            'required' => true,
            'attributes' =>  array(
                'class' => '',
                'id' => 'business[entityTypes]'
                ),
            'options' => array(
                'label' => $this->getResourceString('your-business-form-label-type-of-business'),
            ),
        ));
        
        $entityTypesElem = $this->get('business[entityTypes]');
        $entityTypeGroup = $this->getSelectResourceStrings(self::$entityTypesArray);
        $entityTypesElem->setValueOptions($this->setSelect($entityTypeGroup, 'Select type'));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'business[companyNumber]',
            'required' => true,
            'attributes' =>  array(
                'class' => '',
                'id' => 'business[companyNumber]'
                ),
            'options' => array(
                'label' => $this->getResourceString('your-business-form-label-registered-company-number'),
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'business[companyName]',
            'required' => true,
            'attributes' =>  array(
                'class' => '',
                'id' => 'business[companyName]'
                ),
            'options' => array(
                'label' => $this->getResourceString('your-business-form-label-registered-company-name'),
            ),
        ));
                
        

        $this->add(array(
            'name' => 'organisationSearchBtn',
            'type' => 'button',
            'attributes' => array(
                'class' => 'inline-btn',
                'id' => 'organisationSearchBtn',
                'value' => 'Search'
            ),            
            'options' => array(
                'label' => $this->getResourceString('common-button-find'),
            ),
        ));        
        
        $this->add(array(
            'name' => 'business[tradingNameId]',
            'type' => 'Text',
            'attributes' => array(
                'class' => '',
                'id' => 'business[tradingNameId]',
            ),  
            'options' => array(
                'label' => $this->getResourceString('your-business-form-label-trading-name'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'business[tradingNames]',
            'options' => array(
                'count' => 0,
                'target_element' => array(
                    'type' => 'Zend\Form\Element\Hidden',
                    'id' => 'business[tradingNames]',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'tradingAddAnother',
            'type' => 'button',
            'attributes' => array(
                'class' => '',
                'id' => 'tradingAddAnother',
                'value' => 'Search'
            ),            
            'options' => array(
                'label' => $this->getResourceString('common-button-add-another'),
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'business[businessTypes]',
            'required' => true,
            'attributes' =>  array(
                'class' => '',
                'id' => 'business[businessTypes]'
                ),
            'options' => array(
                'label' => $this->getResourceString('your-business-form-label-types-of-business'),
            ),
        ));

        $entityTypesElem = $this->get('business[businessTypes]');
        $entityTypeGroup = $this->getSelectResourceStrings(self::$businessTypesArray);
        $entityTypesElem->setValueOptions($this->setSelect($entityTypeGroup, $this->getResourceString('common-please-select')));

        
        
        
        
    }
        

    public function isValid() {
        return true;
    }
}

