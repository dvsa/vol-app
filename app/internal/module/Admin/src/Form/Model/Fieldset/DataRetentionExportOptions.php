<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Class DataRetentionExportOptions
 */
class DataRetentionExportOptions
{
    #[Form\Type('Select')]
    #[Form\Options(['label' => 'Rule'])]
    public $rule = null;

    #[Form\Options(['label' => 'Date from', 'input_type' => 'Common\InputFilter\DateParts'])]
    #[Form\Required(true)]
    #[Form\Type('DateSelect')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    public $startDate = null;

    #[Form\Options(['label' => 'Date to', 'input_type' => 'Common\InputFilter\DateParts'])]
    #[Form\Required(true)]
    #[Form\Type('DateSelect')]
    #[Form\Validator('Date', options: ['format' => 'Y-m-d'])]
    #[Form\Validator('DateCompare', options: ['has_time' => false, 'compare_to' => 'startDate', 'operator' => 'gte', 'compare_to_label' => 'Start date'])]
    public $endDate = null;
}
