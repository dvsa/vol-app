<?php
namespace Permits\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator;

class TripsForm extends Form
{
    private $inputFilter;

    public function __construct($name = null)
    {
        parent::__construct();

        $this->inputFilter = null;

        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));

        $this->add(array(
            'name' => 'numberOfTrips',
            'type' => Element\Number::class,
            'options' => array(
                'label' => '',
            ),
            'attributes' => [
                'class' => 'input--trips',
            ],
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Save and continue',
                'id' => 'submitbutton',
                'class' => 'action--primary large',
            ),
        ));
    }

    public function getInputFilter()
    {
        if($this->inputFilter == null)
        {
            $this->inputFilter = new InputFilter();

            $this->inputFilter->add([
                'name'     => 'numberOfTrips',
                'required' => true,
                'filters'  => [],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 300,
                        ]
                    ],
                ]
            ]);
        }

        return $this->inputFilter;
    }
}
