<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 */
#[Form\Attributes(['class' => 'table__form'])]
#[Form\Name('fields')]
class DelayItem
{
    #[Form\Required(false)]
    #[Form\Type('DateSelect')]
    #[Form\Attributes(['id' => 'nextReviewDate', 'data-container-class' => 'nextReviewDate'])]
    #[Form\Options(['input_type' => 'Common\InputFilter\DateParts', 'label' => 'Next review date:', 'create_empty_option' => true])]
    #[Form\Input('Common\InputFilter\DateParts')]
    #[Form\Filter('DateSelectNullifier')]
    #[Form\Validator('\Common\Validator\Date')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    public $nextReviewDate = null;
}
