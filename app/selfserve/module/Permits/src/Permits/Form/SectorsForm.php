<?php
namespace Permits\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class SectorsForm extends Form
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
            'type' => 'MultiCheckBox',
            'name' => 'sectors',
            'options' => array(
                'label' => '',
                'label_attributes' => array(
                    'class' => 'form-control form-control--checkbox',
                ),
                'value_options' => array(
                    'Food' => 'Food products',
                    'Mail' => 'Mail and parcels',
                    'Transport' => 'Transport equipment',
                    'Metal' => 'Metal',
                    'Chemicals' => 'Chemicals',
                    'Non-Metallic' => 'Non-Metallic mineral product',
                    'Wood' => 'Wood',
                    'Furniture' => 'Furniture',
                    'Raw-materials' => 'Raw materials',
                    'Coke-petroleum' => 'Coke and refined petroleum products',
                    'Textiles' => 'Textiles',
                    'Chemicals' => 'Chemicals',
                    'Other' => 'Other',
                ),
            )
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
                'name'     => 'sectors',
                'required' => true,
                'filters'  => [],
//                'validators' => [
//                    [
//                        'name' => 'Regex',
//                        'options' => [
//                            'pattern' => '/[Food|Mail|Transport|Metal|Chemicals|Non-Metallic|Wood|Furniture|Raw-materials|Coke-petroleum|Textiles|Chemicals|Other]/'
//                        ]
//                    ],
//                ]
            ]);
        }

        return $this->inputFilter;
    }
}
