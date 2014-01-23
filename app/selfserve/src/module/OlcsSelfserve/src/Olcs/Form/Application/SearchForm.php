<?php
/**
 *  Operator Search Form
 *
 *  @author     J Rowbottom <jess.rowbottom@valtech.co.uk>
 *  @package    olcs
 *  @subpackage application
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;

class SearchForm extends Form
{


    public function __construct($name = null)  {
        // we want to ignore the name passed
        parent::__construct('applicationSearchForm');
        $this->setAttribute('class', 'application-search-form form-horizontal overlay-form');
        $this->setAttribute('action', '/application/operator/search');

        $this->add(array(
            'name' => 'popupOperatorName',
            'type' => 'Text',
            'attributes' =>  array(
                'class' => 'form-control',
                'id' => 'popupOperatorName'
            )
        ));

        $this->add(array(
            'name' => 'popupOperatorType',
            'type' => 'Hidden',
            'attributes' => array(
                'class' => 'form-control column-operator-type',
                'id' => 'operatorTypeHidden',
            )
        ));

        $this->add(array(
            'name' => 'search',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-success btn-next',
                'id' => 'popupSearchbutton',
                'value' => 'Search',
                'type' => 'submit',
            )
        ));
        
        $this->add(array(
            'name' => 'new',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-success btn-next',
                'id' => 'popupNewbutton',
                'value' => 'New'
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

