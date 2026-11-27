<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Create variation fieldset
 */
class CreateVariation
{
    #[Form\Type('DateSelect')]
    #[Form\Input('Common\InputFilter\DateParts')]
    #[Form\Options(['input_type' => 'Common\InputFilter\DateParts', 'label' => 'create-variation-application-received-date'])]
    #[Form\Validator('\Common\Validator\Date')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    #[Form\Filter('DateSelectNullifier')]
    public $receivedDate;

    #[Form\Type('Radio')]
    #[Form\Options(['label' => 'create-variation-fee-required', 'value_options' => ['Y' => 'Yes', 'N' => 'No'], 'fieldset-attributes' => ['class' => 'inline']])]
    public $feeRequired;

    #[Form\Type('Radio')]
    #[Form\Options(['label' => 'Method of application', 'value_options' => ['applied_via_post' => 'Post', 'applied_via_phone' => 'Phone'], 'fieldset-attributes' => ['class' => 'inline']])]
    protected $appliedVia = null;
}
