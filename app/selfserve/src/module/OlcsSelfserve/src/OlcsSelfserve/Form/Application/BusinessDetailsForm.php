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
            'id' => 'business[entityTypes]',
            'type' => 'Zend\Form\Element\Select',
            'name' => 'business[entityTypes]',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'business[entityTypes]'
                )
        ));
        
        $entityTypesElem = $this->get('business[entityTypes]');
        $entityTypeGroup = $this->getSelectResourceStrings(self::$entityTypesArray);
        $entityTypesElem->setValueOptions($this->setSelect($entityTypeGroup, 'Select type'));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'business[companyNumber]',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control',
                'id' => 'business[companyNumber]'
                )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'business[companyName]',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control',
                'id' => 'business[companyName]'
                )
        ));
                
        

        $this->add(array(
            'name' => 'organisationSearchBtn',
            'type' => 'button',
            'attributes' => array(
                'class' => 'form-control btn btn-search ',
                'id' => 'organisationSearchBtn',
                'value' => 'Search'
            )
        ));        
        
        $this->add(array(
            'name' => 'business[tradingNameId]',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'business[tradingNameId]',
            )
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
                'class' => 'btn btn-add disabled',
                'id' => 'tradingAddAnother',
                'value' => 'Search'
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'business[businessTypes]',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'business[businessTypes]'
                )
        ));

        $entityTypesElem = $this->get('business[businessTypes]');
        $entityTypeGroup = $this->getSelectResourceStrings(self::$businessTypesArray);
        $entityTypesElem->setValueOptions($this->setSelect($entityTypeGroup, $this->getResourceString('common-please-select')));
        
    }
        

    public function isValid() {
        return true;
    }
}

