<?php
/**
 * Self-serve Licence Type application
 *
 * @package    selfserve
 * @subpackage application
 * @author     Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace OlcsSelfserve\Form\Application;

use Zend\Form\Form;

class LicenceTypeForm extends Form
{
    public function __construct($name = null)  {
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
