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

class DetailsForm extends Form
{
    public function __construct($name = null)  {
        // we want to ignore the name passed
        parent::__construct('vcaseDetailsForm');
        $this->setAttribute('action', '/case/convictions');

        $this->add(array(
            'name' => 'caseId',
            'type' => 'hidden',
             'attributes' =>  array(
                'id' => 'caseId'
            )
        ));
        $this->add(array(
            'name' => 'caseDetailsNote',
            'type' => 'textarea',
             'attributes' =>  array(
                'id' => 'caseDetailsNote',
                'data-mce-size' => '300',
                'class' => 'tmce',
            )
        ));
        $this->add(array(
            'name' => 'commentId',
            'type' => 'hidden',
             'attributes' =>  array(
                'id' => 'commentId'
            )
        ));
        $this->add(array(
            'name' => 'detailTypeId',
            'type' => 'hidden',
             'attributes' =>  array(
                'id' => 'detailTypeId',
                'value' => 2
            )
        ));
    }
}
