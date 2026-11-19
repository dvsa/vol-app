<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Attributes(['class' => ''])]
#[Form\Name('tm-employer-name-details')]
class TmEmployerNameDetails
{
    #[Form\Attributes(['class' => 'long', 'id' => 'employerName'])]
    #[Form\Options(['label' => 'transport-manager.employment.form.employerName'])]
    #[Form\Required(true)]
    #[Form\Type('Text')]
    #[Form\Filter('Laminas\Filter\StringTrim')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['max' => 90])]
    public $employerName = null;
}
