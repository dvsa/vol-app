<?php

namespace Olcs\Form\VCase;

use Zend\Form\Form;
use Olcs\Form\OlcsForm;

class NewForm extends OlcsForm
{

    public function __construct($name = null)  {
        // we want to ignore the name passed
        parent::__construct('vcaseNewForm');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('action', '/case/new');

        //Todo: Add hidden element with the id to bas ethe new case on
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'categories',
            'required' => true,
            'attributes' =>  array(
                'multiple' => 'multiple',
                'class' => 'form-control multiselect',
                'id' => 'categories'
            )
        ));
        $this->add(array(
            'name' => 'description',
            'type' => 'Textarea',
            'required' => true,
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'description',
            )
        ));
        $this->add(array(
            'name' => 'ecms',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ecms',
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'btn btn-success btn-next',
                'id' => 'submitbutton',
                'value' => 'Next'
            )
        ));

        $this->add(array(
            'name' => 'operatorId',
            'type' => 'hidden'
            
        ));
		
        $this->add(array(
            'name' => 'licenceId',
            'type' => 'hidden'
            
        ));
        
        $this->add(array(
            'name' => 'licenceNumber',
            'type' => 'hidden'
            
        ));
        
         $this->add(array(
            'name' => 'operatorName',
            'type' => 'hidden'
            
        ));


        $categories = $this->get('categories');
        $categories->setValueOptions($this->getCategories());
    }

    private function getCategories() {
        $options = array();
        foreach (self::$categories as $mainCategory => $subCategories) {
            $options[] = array(
                'label' => $mainCategory,
                'options' => $subCategories
            );
        }
        return $options;
    }
}
