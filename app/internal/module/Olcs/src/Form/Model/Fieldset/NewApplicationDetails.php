<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
#[Form\Name('new-application-details')]
class NewApplicationDetails
{
    #[Form\Attributes(['id' => 'receivedDate'])]
    #[Form\Options(['input_type' => 'Common\InputFilter\DateParts', 'label' => 'Application received', 'create_empty_option' => true, 'render_delimiters' => false])]
    #[Form\Filter('DateSelectNullifier')]
    #[Form\Validator('\Common\Validator\Date')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    #[Form\Validator('\Common\Form\Elements\Validators\DateNotInFuture')]
    #[Form\Type('DateSelect')]
    #[Form\Input('Common\InputFilter\DateParts')]
    public $receivedDate = null;

    #[Form\Attributes(['id' => 'trafficArea', 'placeholder' => ''])]
    #[Form\Options(['label' => 'Traffic area', 'disable_inarray_validator' => false])]
    #[Form\Type('Select')]
    public $trafficArea = null;
}
