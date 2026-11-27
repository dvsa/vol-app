<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
#[Form\Name('fields')]
class SlaTargetDate extends Base
{
    #[Form\Required(false)]
    #[Form\Attributes(['id' => 'entityTypeHtml', 'required' => false])]
    #[Form\Options(['label' => 'Document:'])]
    #[Form\Type('Common\Form\Elements\Types\Html')]
    public $entityTypeHtml;

    #[Form\Type('Hidden')]
    public $entityId = null;

    #[Form\Type('Hidden')]
    public $entityType = null;

    #[Form\Options(['input_type' => 'Common\InputFilter\DateParts', 'label' => 'Received/Agreed date', 'create_empty_option' => false, 'render_delimiters' => true])]
    #[Form\Type('DateSelect')]
    #[Form\Input('Common\InputFilter\DateParts')]
    #[Form\Filter('DateSelectNullifier')]
    #[Form\Validator('\Common\Validator\Date')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    public $agreedDate = null;

    #[Form\Attributes(['id' => 'sentDate'])]
    #[Form\Options(['input_type' => 'Common\InputFilter\DateParts', 'label' => 'Sent date', 'create_empty_option' => true, 'render_delimiters' => false])]
    #[Form\Input('Common\InputFilter\DateParts')]
    #[Form\Required(false)]
    #[Form\Type('DateSelect')]
    #[Form\Filter('DateSelectNullifier')]
    #[Form\Validator('\Common\Validator\Date')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    #[Form\Validator('\Common\Form\Elements\Validators\DateNotInFuture')]
    #[Form\Validator('ValidateIf', options: ['context_field' => 'agreedDate', 'context_values' => ['--'], 'context_truth' => false, 'allow_empty' => true, 'validators' => [['name' => 'Date', 'options' => ['format' => 'Y-m-d']], ['name' => 'DateCompare', 'options' => ['compare_to' => 'agreedDate', 'compare_to_label' => 'Agreed date', 'operator' => 'gte']]]])]
    public $sentDate = null;

    #[Form\Attributes(['id' => 'targetDate'])]
    #[Form\Options(['input_type' => 'Common\InputFilter\DateParts', 'label' => 'Target date', 'create_empty_option' => true, 'render_delimiters' => false])]
    #[Form\Input('Common\InputFilter\DateParts')]
    #[Form\Required(false)]
    #[Form\Type('DateSelect')]
    #[Form\Filter('DateSelectNullifier')]
    #[Form\Validator('\Common\Validator\Date')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    #[Form\Validator('ValidateIf', options: ['context_field' => 'agreedDate', 'context_values' => ['--'], 'context_truth' => false, 'allow_empty' => true, 'validators' => [['name' => 'Date', 'options' => ['format' => 'Y-m-d']], ['name' => 'DateCompareWithInterval', 'options' => ['compare_to' => 'agreedDate', 'compare_to_label' => 'Agreed date', 'interval_label' => '5 working days', 'date_interval' => 'P5D', 'operator' => 'gt']]]])]
    public $targetDate = null;

    #[Form\Type('Radio')]
    #[Form\Options(['label' => 'Under delegation?', 'value_options' => ['N' => 'No', 'Y' => 'Yes'], 'fieldset-attributes' => ['class' => 'inline']])]
    #[Form\Attributes(['value' => 'N'])]
    public $underDelegation;

    #[Form\Attributes(['class' => 'extra-long', 'id' => ''])]
    #[Form\Options(['label' => 'Notes'])]
    #[Form\Required(false)]
    #[Form\Type('TextArea')]
    #[Form\Filter('Laminas\Filter\StringTrim')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['min' => 5, 'max' => 4000])]
    public $notes = null;
}
