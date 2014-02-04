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

use OlcsSelfserve\Form\OlcsSelfserveForm;

class BusinessDetailsForm extends OlcsSelfserveForm
{
    public $tradingBusinessArray = array('TO FIX' => 'Pre selected SIC code TO FIX');
    
    public function __construct()  {

        parent::__construct('BusinessDetailsForm');
        
        $this->add(array(
            'id' => 'entityType',
            'type' => 'Zend\Form\Element\Select',
            'name' => 'entityType',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'entityType'
                )
        ));
        
        $entityTypesElem = $this->get('entityType');
        $entityTypeGroup = $this->getSelectResourceStrings(self::$entityTypesArray);
        $entityTypesElem->setValueOptions($this->setSelect($entityTypeGroup, 'Select type'));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'companyNumId',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control',
                'id' => 'companyNumId'
                )
        ));
      
        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'operatorId',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control',
                'id' => 'operatorId'
                )
        ));
                
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'operatorName',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control',
                'id' => 'operatorName'
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
                'class' => 'btn btn-add',
                'id' => 'tradingAddAnother',
                'value' => 'Search'
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'licence[trade_type]',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'licence[trade_type]'
                )
        ));
        
        $entityTypesElem = $this->get('licence[trade_type]');
        $entityTypeGroup = $this->getSelectResourceStrings($this->tradingBusinessArray);
        $entityTypesElem->setValueOptions($this->setSelect($this->tradingBusinessArray, 'Select type'));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'licence[tradingother]',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control',
                'id' => 'licence[tradingother]'
                )
        ));
        
        
        
        $this->add(array(
            'name' => 'savenext',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-next disabled',
                'id' => 'savenextbutton',
                'value' => 'Save & Next'
            )
        ));

        $this->add(array(
            'name' => 'back',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-back',
                'id' => 'backbutton',
                'value' => 'Back'
            )
        ));

        $this->add(array(
            'name' => 'exit',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-exit',
                'id' => 'exitbutton',
                'value' => 'Exit'
            )
        ));
    }
        
    /**
     * BusinessDetailsForm validation
     * 
     * @return boolean
     */
    public function isValid() {
        
        return true;
    }
}

