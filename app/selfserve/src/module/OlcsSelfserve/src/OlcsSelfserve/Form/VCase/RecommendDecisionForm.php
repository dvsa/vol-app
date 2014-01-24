<?php
/**
 * Case Details Zend Form.
 *
 * Defines form elements to be shown on the Case details page
 *
 * @package		olcs
 * @subpackage	vcase
 * @author		Mike Cooper
 */

namespace Olcs\Form\VCase;

use Zend\Form\Form;

class RecommendDecisionForm extends Form
{
    
    public $submitActionTypes = array('Recommendation' => 'Recommendation',
                                                                'Decision' => 'Decision');
    
    public $recommendActions = array('Other' => 'Other',
                                                            'Propose to revoke' => 'Propose to revoke',
                                                            'Warning letter' => 'Warning letter',
                                                            'No further action' => 'No further action',
                                                            'Undertakings' => 'Undertakings',
                                                            'Public inquiry' => 'Public inquiry' );
    
    public $decisionActions = array('Agree' => 'Agree',
                                                            'Partially agree' => 'Partially agree',
                                                            'Disagree' => 'Disagree',
                                                            'Further info required' => 'Further info required');
    
    private $resourceHelper;
    
    public function __construct($name = null)  {
        
        $resources = $this->getResourceStrings();
        $resourceHelper = new \Olcs\View\Helper\ResourceHelper($resources);
        // we want to ignore the name passed
        parent::__construct('recommendDecisionForm');
        $this->setAttribute('action', '/case/submission/send');

        //Todo: Add hidden element with the id to bas ethe new case on
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'submitActionTypes',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'submitActionTypes'
            )
        ));
        $submitActionTypesElem = $this->get('submitActionTypes');
        $submitActionTypes = $this->getSelectResourceStrings($this->submitActionTypes);
        $submitActionTypesElem->setValueOptions($this->setSelect($submitActionTypes, 'Choose one'));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'recommendActions',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'recommendActions',
                 'disabled' => 'disabled'
            )
        ));
        $recommendActionsElem = $this->get('recommendActions');
        $recommendActionTypes = $this->getSelectResourceStrings($this->recommendActions);
        $recommendActionsElem->setValueOptions($this->setSelect($recommendActionTypes, 'Select type'));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'decisionActions',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'decisionActions',
                'disabled' => 'disabled'
            )
        ));
        $decisionActionsElem = $this->get('decisionActions');
        $decisionActionTypes = $this->getSelectResourceStrings($this->decisionActions);
        $decisionActionsElem->setValueOptions($this->setSelect($decisionActionTypes, 'Select type'));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'emptyActions',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'emptyActions',
                'disabled' => 'disabled'
            )
        ));
        $emptyActionsElem = $this->get('emptyActions');
        $emptyActionsElem->setValueOptions($this->setSelect(array(), 'Select type'));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'recipientUserId',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'recipientUserId',
            )
        ));
        
        $this->add(array(
            'name' => 'senderUserId',
            'type' => 'hidden',
             'attributes' =>  array(
                'id' => 'senderUserId'
            )
        ));
        $this->add(array(
            'name' => 'submissionId',
            'type' => 'hidden',
             'attributes' =>  array(
                'id' => 'submissionId'
            )
        ));
        $this->add(array(
            'name' => 'caseId',
            'type' => 'hidden',
             'attributes' =>  array(
                'id' => 'caseId'
            )
        ));
        $this->add(array(
            'name' => 'licenceId',
            'type' => 'hidden',
             'attributes' =>  array(
                'id' => 'licenceId'
            )
        ));
        $this->add(array(
            'name' => 'other',
            'type' => 'text',
            'required' => true,
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'other',
                'disabled' => 'disabled'
            )
        ));
        $this->add(array(
            'name' => 'urgent',
            'type' => 'checkbox',
            'required' => true,
            'attributes' => array(
                'id' => 'urgent',
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-submit',
                'id' => 'submitbutton',
                'value' => 'Send',
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
    
}
