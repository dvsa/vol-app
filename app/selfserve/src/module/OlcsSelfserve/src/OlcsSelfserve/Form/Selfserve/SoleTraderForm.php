<?php
/**
 *  Sole Trader Details Form
 *
 *  @author     Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 *  @package    olcs
 *  @subpackage application
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;

class SoleTraderForm extends Form
{
    public $tradingBusinessArray = array('Skip hire / waste transfer / refuse' => 'Skip hire waste transfer refuse',
                                            'Gen haulier / distribution' => 'Gen haulier distribution',
                                            'Farmers / Livestock carriers' => 'Farmers Livestock carriers',
                                            'Removals / Construction / Plant hire' => 'Removals Construction Plant hire',
                                            'Utilities' => 'Utilities',
                                            'Dangerous goods / hazchem carriers' => 'Dangerous goods hazchem carriers',
                                            'Refrigerated transport' => 'Refrigerated transport',
                                            'Other' => 'Other');
    public function __construct($name = null)  {
        // we want to ignore the name passed
        parent::__construct('SoleTraderForm');
        $this->setAttribute('class', 'application-new-form form-horizontal');
        $this->setAttribute('action', '/application/new');

        $hiddenElement = array(
            'type' => 'Hidden',
            'attributes' => array(
                'class' => 'form-control',
            )
        );

        $this->add(array(
            'name' => 'tradingNameId',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'tradingNameId',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'tradingNames',
            'options' => array(
                'count' => 0,
                'target_element' => array(
                    'type' => 'Zend\Form\Element\Hidden',
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
            'name' => 'tradingDetails',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'tradingDetails',
            )
        ));

        $this->add(array(
            'name' => 'tradingOther',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control hidden',
                'id' => 'tradingOther',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'tradingDropdown',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'tradingDropdown'
                )
        ));
        $tradingTypesElem = $this->get('tradingDropdown');
        $tradingTypeGroup = $this->getSelectResourceStrings($this->tradingBusinessArray);
        $tradingTypesElem->setValueOptions($this->setSelect($tradingTypeGroup, 'Select type'));

    }
    
    public function isValid() {
        return true;
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
}

