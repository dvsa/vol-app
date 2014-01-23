<?php
/**
 *  Application Form
 *
 *  @author     J Rowbottom <joel.rowbottom@valtech.co.uk>
 *  @package    olcs
 *  @subpackage application
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;

class OperatorEditForm extends Form
{

    public function __construct($name = null)  {
        // we want to ignore the name passed
        parent::__construct('applicationOperatorEditForm');
        $this->setAttribute('class', 'application-operator-edit-form form-horizontal');
        $this->setAttribute('action', '/application/search/operatoredit');
        
        $this->add(array(
            'name' => 'popupOperatorName',
            'type' => 'Text',
            'attributes' =>  array(
                'class' => 'form-control',
                'id' => 'popupOperatorName'
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-save',
                'id' => 'popupSaveOperatorEditButton',
                'type' => 'submit',
                'value' => 'Save'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-success btn-cancel',
                'id' => 'popupCancelbutton',
                'value' => 'Cancel',
            )
        ));
    }

}

