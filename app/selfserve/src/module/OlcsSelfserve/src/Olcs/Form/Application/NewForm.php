<?php
/**
 *  Application Form
 *
 *  @author     Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 *  @package    olcs
 *  @subpackage application
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;

class NewForm extends Form
{
    public $entityTypeArray = array('Registered Company' => 'Registered Company',
                                    'Sole Trader' => 'Sole trader',
                                    'Partnership' => 'Partnership',
                                    'Public Authority' => 'Public Authority',
                                    'Other' => 'Other');

    public $trafficAreaTypeArray = array('North East of England' => 'North East of England',
                                         'North West of England' => 'North West of England',
                                         'West Midlands' => 'West Midlands',
                                         'East of England' => 'East of England',
                                         'West of England' => 'West of England',
                                         'Wales' => 'Wales',
                                         'London and the South East of England' => 'London and the South East of England',
                                         'Northern Ireland' => 'Northern Ireland');



    public function __construct($name = null)  {
        // we want to ignore the name passed
        parent::__construct('applicationNewForm');
        $this->setAttribute('class', 'application-new-form form-horizontal');
        $this->setAttribute('action', '/application/new/details');

        $this->add(array(
            'name' => 'source',
            'type' => 'hidden',
             'attributes' =>  array(
                'id' => 'newApplication',
                'value' => 'newApplication'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'operatorTypes',
            'options' => array(
                        'value_options' => array(
                            'psv' => "PSV",
                            'goods' => "Goods"
                        )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'licenceTypes',
            'attributes' =>  array(
                'id' => 'licenceTypes'
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'entityTypes',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'entityTypes'
                )
        ));
        
        $entityTypesElem = $this->get('entityTypes');
        $entityTypeGroup = $this->getSelectResourceStrings($this->entityTypeArray);
        $entityTypesElem->setValueOptions($this->setSelect($entityTypeGroup, 'Select type'));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'trafficAreaType',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'trafficAreaType'
                )
        ));
        $trafficAreaTypeElem = $this->get('trafficAreaType');
        $trafficAreaTypeGroup = $this->getSelectResourceStrings($this->trafficAreaTypeArray);
        $trafficAreaTypeElem->setValueOptions($this->setSelect($trafficAreaTypeGroup, 'Select type'));

        $this->add(array(
            'name' => 'dateApplicationReceived',
            'type' => 'Olcs\Form\Element\DateSelect',
            'required' => true,
            'options' => array(
                'day_attributes' =>  array(
                    'class' => 'multiselect',
                ),
                'month_attributes' =>  array(
                    'class' => 'multiselect',
                ),
                'year_attributes' =>  array(
                    'class' => 'form-control',
                ),
            ),
        ));

        $dateApplicationReceived = $this->get('dateApplicationReceived');
        $dateApplicationReceived->setValue(new \DateTime());

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-next',
                'id' => 'submitbutton',
                'value' => 'Next'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-success btn-cancel',
                'id' => 'cancelbutton',
                'value' => 'Cancel'
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

    private function getDays() {

        $days = array(''=>'Day');
        foreach (range(1, 31) as $number) {
            $days[$number] = $number;
        }
        return $days;

    }
    
    public function isValid() {
        return true;
    }
}

