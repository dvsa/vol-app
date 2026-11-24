<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Name('licence-decision-surrender')]
class LicenceStatusDecisionSurrender
{
    #[Form\Type('DateSelect')]
    #[Form\Filter('DateSelectNullifier')]
    #[Form\Required(true)]
    #[Form\Attributes(['required' => false])]
    #[Form\Options(['label' => 'licence-status.surrender.date.label', 'create_empty_option' => true, 'min_year_delta' => '-5'])]
    #[Form\Validator('\Laminas\Validator\NotEmpty')]
    #[Form\Validator('\Common\Validator\Date')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    #[Form\Validator('\Common\Form\Elements\Validators\DateNotInFuture')]
    public $surrenderDate = null;
}
