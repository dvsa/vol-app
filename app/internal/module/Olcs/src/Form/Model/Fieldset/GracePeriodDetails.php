<?php

/**
 * GraceperiodDetails.php
 */

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Name('grace-period-details')]
class GracePeriodDetails
{
    #[Form\Options(['label' => 'internal-licence-grace-periods-period-details-gracePeriodType', 'empty_option' => 'Please Select', 'disable_inarray_validator' => false, 'category' => 'grace_period_type'])]
    #[Form\Required(true)]
    #[Form\Attributes(['id' => 'gracePeriodType'])]
    #[Form\Type('DynamicSelect')]
    public $gracePeriodType = null;

    #[Form\Required(true)]
    #[Form\Attributes(['id' => 'startDate'])]
    #[Form\Options(['input_type' => 'Common\InputFilter\DateParts', 'label' => 'internal-licence-grace-periods-period-details-startDate', 'create_empty_option' => true, 'render_delimiters' => false, 'max_year_delta' => '+5', 'min_year_delta' => '-5'])]
    #[Form\Type('DateSelect')]
    #[Form\Input('Common\InputFilter\DateParts')]
    #[Form\Validator('\Common\Validator\Date')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d', 'messages' => ['dateInvalidDate' => 'datetime.compare.validation.message.invalid']])]
    #[Form\Filter('DateSelectNullifier')]
    public $startDate = null;

    #[Form\Required(true)]
    #[Form\Attributes(['id' => 'endDate'])]
    #[Form\Options(['input_type' => 'Common\InputFilter\DateParts', 'label' => 'internal-licence-grace-periods-period-details-endDate', 'create_empty_option' => true, 'render_delimiters' => false, 'max_year_delta' => '+5', 'min_year_delta' => '-5'])]
    #[Form\Type('DateSelect')]
    #[Form\Input('Common\InputFilter\DateParts')]
    #[Form\Validator('ValidateIf', options: ['context_field' => 'startDate', 'context_values' => ['--'], 'context_truth' => false, 'allow_empty' => true, 'validators' => [['name' => '\Common\Validator\Date'], ['name' => 'Date', 'options' => ['format' => 'Y-m-d', 'messages' => ['dateInvalidDate' => 'datetime.compare.validation.message.invalid']], 'break_chain_on_failure' => true], ['name' => 'DateCompare', 'options' => ['has_time' => false, 'compare_to' => 'startDate', 'operator' => 'gt', 'compare_to_label' => 'start date']]]])]
    #[Form\Filter('DateSelectNullifier')]
    public $endDate = null;

    #[Form\Attributes(['class' => 'extra-long', 'id' => ''])]
    #[Form\Options(['label' => 'internal-licence-grace-periods-period-details-description'])]
    #[Form\Required(false)]
    #[Form\Type('TextArea')]
    #[Form\Filter('Laminas\Filter\StringTrim')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['min' => 1, 'max' => 90])]
    public $description = null;
}
