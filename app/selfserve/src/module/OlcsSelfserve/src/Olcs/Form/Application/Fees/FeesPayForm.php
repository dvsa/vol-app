<?php
/**
 * Generic form used to track added and removed ID:s in a list of entities
 *
 * @package    olcs
 * @subpackage application
 * @author     Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Form\Application\Fees;

use Olcs\Form\OlcsForm;

class FeesPayForm extends OlcsForm
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        
        $this->add(array(
            'name' => 'formPaymentType',
            'type' => 'hidden',
            'attributes' => array(
                'id' => 'formPaymentType',
            ),
        ));
        $this->add(array(
            'label' => $this->getResourceString('receipt'),
            'name' => 'receipt',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'receipt',
            ),
        ));
        $this->add(array(
            'label' => $this->getResourceString('pay-now'),
            'name' => 'payNowButton',
            'type' => 'submit',
            'attributes' => array(
                'class' => 'btn btn-success pull-right',
                'value' => 'Pay now',
                'id'    => 'payNowButton'
            ),
        ));
        $this->add(array(
            'label' => 'cancel',
            'name' => 'payCancel',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-success btn-cancel pull-left',
                'id' => 'payCancel',
                'value' => 'Cancel',
            )
        ));
    }
}
