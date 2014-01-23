<?php
/**
 * Case Details Zend Form.
 *
 * Defines form elements to be shown in the case actions form
 *
 * @package		olcs
 * @subpackage	vcase
 * @author		Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Form\VCase;

use Zend\Form\Form;
use Olcs\Form\OlcsForm;

class ActionForm extends OlcsForm
{
    public function __construct($caseId, $url = null)  {
        // we want to ignore the name passed
        parent::__construct('vcaseActionForm');

        if ($url) {
            $this->setAttribute('action', $url);
        }

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'submitActionTypes',
            'required' => true,
            'attributes' =>  array(
                'class' => 'form-control multiselect',
                'id' => 'submitActionTypes'
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
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-submit btn-go',
                'id' => 'submitbutton',
                'value' => 'Go'
            )
        ));

        $this->get('submitActionTypes')->setValueOptions($this->getCaseSubmitActionTypes());
        $this->get('caseId')->setValue($caseId);
    }

    /**
     * Gets the select options for submit actions
     */
    private function getCaseSubmitActionTypes() {
        $options = array('' => 'Please select');
        foreach (self::$caseDetailTypes as $key => $option) {
            $options[$key] = $option;
        }
        return $options;
    }

    public static function handleAction($actionType, $controller) {
        switch ($actionType) {
            case "1":
                return $controller->forward()->dispatch('Olcs\Controller\VCase\Submission', array(
                    'action' => 'generator',
                    'caseId' => $controller->getRequest()->getPost('caseId'),
                    'licenceId' => $controller->getRequest()->getQuery('licenceId') ?
                                                $controller->getRequest()->getQuery('licenceId') :
                                                    $controller->getRequest()->getPost('licenceId')));
                break;

            default:
                return false;
        }
    }
}
