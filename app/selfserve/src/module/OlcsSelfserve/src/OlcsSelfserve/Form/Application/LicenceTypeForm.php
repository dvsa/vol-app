<?php
/**
 * Self-serve Licence Type application
 *
 * @package    selfserve
 * @subpackage application
 * @author     Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace OlcsSelfserve\Form\Application;

use OlcsSelfserve\Form\OlcsSelfserveForm;

class LicenceTypeForm extends OlcsSelfserveForm
{
    public $tradingBusinessArray = array('Skip hire / waste transfer / refuse' => 'Skip hire waste transfer refuse',
                                        'Gen haulier / distribution' => 'Gen haulier distribution',
                                        'Farmers / Livestock carriers' => 'Farmers Livestock carriers',
                                        'Removals / Construction / Plant hire' => 'Removals Construction Plant hire',
                                        'Utilities' => 'Utilities',
                                        'Dangerous goods / hazchem carriers' => 'Dangerous goods hazchem carriers',
                                        'Refrigerated transport' => 'Refrigerated transport',
                                        'Other' => 'Other');

    public function __construct($name = null)  
    {
        // we want to ignore the name passed
        parent::__construct('LicenceTypeForm');
        $this->setAttribute('class', 'application-new-form form-horizontal');
        $this->setAttribute('action', '/selfserve/licence-type');

        $hiddenElement = array(
            'type' => 'Hidden',
            'attributes' => array(
                'class' => 'form-control',
            )
        );

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'licenceType',
            'attributes' =>  array(
                'id' => 'licenceType'
            ),
            'options' => array(
                        'value_options' => array(
                            'restricted' => "Restricted",
                            'standard national' => "Standard National",
                            'standard international' => "Standard International",
                            'special restricted' => "Special Restricted"
                        )
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'operatorLocation',
            'attributes' =>  array(
                'id' => 'operatorLocation'
            ),
            'options' => array(
                        'value_options' => array(
                            'uk' => "Mainland UK",
                            'northern ireland' => "Northern Ireland"
                        )
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'operatorType',
            'attributes' =>  array(
                'id' => 'operatorType'
            ),
            'options' => array(
                        'value_options' => array(
                            'goods' => "Goods",
                            'psv' => "PSV"
                        )
            )
        ));

        
        $this->add(array(
            'id' => 'licence[trade_type]',
            'type' => 'Zend\Form\Element\Select',
            'name' => 'licence[trade_type]',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'entityType'
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
}
