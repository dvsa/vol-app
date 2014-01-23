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

class RegisteredCompanyDetailsForm extends Form
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
        parent::__construct('RegisteredCompanyDetailsForm');
        $this->setAttribute('class', 'application-new-form form-horizontal');
        $this->setAttribute('action', '/application/new');

        $hiddenElement = array(
            'type' => 'Hidden',
            'attributes' => array(
                'class' => 'form-control',
            )
        );

        $this->add(array_merge($hiddenElement, array(
            'name' => 'entityTypes',
        )));
        $this->add(array_merge($hiddenElement, array(
            'name' => 'licenceTypes',
        )));
        $this->add(array_merge($hiddenElement, array(
            'name' => 'operatorTypes',
        )));
        $this->add(array_merge($hiddenElement, array(
            'name' => 'trafficAreaType',
        )));
        $this->add(array_merge($hiddenElement, array(
            'name' => 'dateApplicationReceived',
        )));

        $this->add(array(
            'name' => 'companyNumId',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'companyNumId',
            )
        ));
        $this->add(array(
            'name' => 'mainOperatorName',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control column-operator-name operator-reset-after-mapping operator-hide-after-mapping',
                'id' => 'mainOperatorName',
                'data-render-value-as' => '<div id="operatorNameText" class="help-block"><a href="#" id="editOperatorName" class="value"></a> <a href="#" id="removeOperatorName">X</a></div>',
            )
        ));
        $this->add(array(
            'name' => 'operatorNameTextHidden',
            'type' => 'Hidden',
            'attributes' => array(
                'class' => 'form-control column-operator-name',
                'id' => 'operatorNameTextHidden',
            )
        ));

        $this->add(array_merge($hiddenElement, array(
            'name' => 'operatorId',
            'attributes' => array(
                'class' => 'form-control primary-operator-id',
            )
        )));

        $this->add(array_merge($hiddenElement, array(
            'name' => 'operatorVersion',
            'attributes' => array(
                'class' => 'form-control primary-operator-version',
            )
        )));

        $this->add(array(
            'name' => 'search',
            'type' => 'button',
            'attributes' => array(
                'class' => 'btn btn-search disabled',
                'id' => 'searchOperatorButton',
                'value' => 'Search'
            )
        ));

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

