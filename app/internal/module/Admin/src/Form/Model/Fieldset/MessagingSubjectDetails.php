<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Common\Form\Model\Form\Traits\VersionTrait;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 */
#[Form\Attributes(['class' => 'table__form'])]
#[Form\Name('messaging-subject-details')]
class MessagingSubjectDetails
{
    use VersionTrait;
    use IdTrait;

    #[Form\Attributes(['placeholder' => '', 'class' => 'long'])]
    #[Form\Options(['label' => 'Description'])]
    #[Form\Type('Text')]
    #[Form\Required(true)]
    #[Form\Filter('Laminas\Filter\StringTrim')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['max' => 249])]
    public $description = null;

    #[Form\Attributes(['id' => 'category', 'placeholder' => ''])]
    #[Form\Options(['label' => 'Category', 'service_name' => 'Olcs\Service\Data\Category', 'context' => ['isTaskCategory' => 'Y'], 'empty_option' => 'Please select'])]
    #[Form\Required(true)]
    #[Form\Type('DynamicSelect')]
    public $category = null;

    #[Form\Attributes(['id' => 'subCategory', 'placeholder' => ''])]
    #[Form\Options(['label' => 'Sub-category', 'service_name' => 'Olcs\Service\Data\SubCategory', 'context' => ['isTaskCategory' => 'Y'], 'empty_option' => 'Please select'])]
    #[Form\Required(true)]
    #[Form\Type('DynamicSelect')]
    public $subCategory = null;

    #[Form\Options(['label' => 'Active', 'checked_value' => '1', 'unchecked_value' => '0'])]
    #[Form\Type('OlcsCheckbox')]
    #[Form\Attributes(['class' => '', 'id' => 'isActive'])]
    public $isActive = null;
}
