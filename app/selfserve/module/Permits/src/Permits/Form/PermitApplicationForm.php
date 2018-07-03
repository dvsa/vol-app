<?php
namespace Permits\Form;

use Zend\Form\Form;

class PermitApplicationForm extends Form
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
          'name' => 'intensity',
          'type' => 'Hidden',//number
        ));

        $this->add(array(
          'type' => 'MultiCheckBox',//MultiCheckBox
          'name' => 'sectors',
        ));

        $this->add(array(
          'type' => 'Hidden',//Radio
          'name' => 'restrictedCountries',
          'options' => array(
            'value_options' => array(
              '1' => 'Yes',
              '0' => 'No',
            ),
          ),
        ));

        $this->add(array(
          'type' => 'MultiCheckBox',
          'name' => 'restrictedCountriesList',

        ));

        $this->add(array(
          'name' => 'submit',
          'type' => 'Submit',
          'attributes' => array(
            'value' => 'Accept and continue',
            'id' => 'submitbutton',
            'class' => 'action--primary large',
          ),
        ));
    }

}