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
            'name' => 'business[tradingNameId]',
            'type' => 'Text',
            'attributes' => array(
                'class' => '',
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
    }
        
    public function isValid() {
        return true;
    }
}

