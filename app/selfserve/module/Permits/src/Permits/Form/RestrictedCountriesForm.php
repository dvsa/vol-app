<?php
namespace Permits\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class RestrictedCountriesForm extends Form
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
            'type' => 'Radio',
            'name' => 'restrictedCountries',
            'options' => array(
                'label' => '',
                'label_attributes' => array(
                    'class' => 'form-control form-control--radio restrictedRadio',
                ),
                'value_options' => array(
                    '1' => 'Yes',
                    '0' => 'No',
                ),
            ),
        ));

        $this->add(array(
            'type' => 'MultiCheckBox',
            'name' => 'restrictedCountriesList',
            'options' => $this->getDefaultRestrictedCountriesListFieldOptions(),
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

    public function getDefaultRestrictedCountriesListFieldOptions()
    {
        return array(
            'label' => '',
            'label_attributes' => array(
                'class' => 'form-control form-control--checkbox',
            ),
        );
    }

    public function getInputFilter()
    {
        if($this->inputFilter == null)
        {
            $this->inputFilter = new InputFilter();

            $this->inputFilter->add([
                'name'     => 'restrictedCountries',
                'required' => true,
                'filters'  => [],
                'validators' => [
                    [
                        'name' => 'Regex',
                        'options' => [
                            'pattern' => '/[1|0]/'
                        ]
                    ],
                ]
            ]);

            $this->inputFilter->add([
                'name'     => 'restrictedCountriesList',
                'required' => false,
                'filters'  => [],
            ]);
        }

        return $this->inputFilter;
    }
}
