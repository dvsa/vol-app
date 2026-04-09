<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterChoice
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Choice Key"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    public $choiceKey = null;

    /**
     * @Form\Options({"label": "Label"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    public $label = null;

    /**
     * @Form\Options({"label": "Group Label"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $groupLabel = null;

    /**
     * @Form\Options({
     *     "label": "Input Type",
     *     "value_options": {
     *         "checkbox": "Checkbox",
     *         "radio": "Radio"
     *     }
     * })
     * @Form\Type("Select")
     * @Form\Required(true)
     * @Form\Attributes({"id":"inputType","class":"medium"})
     */
    public $inputType = null;

    /**
     * @Form\Options({"label": "Display Order"})
     * @Form\Required(false)
     * @Form\Type("Number")
     * @Form\Attributes({"class":"short"})
     * @Form\Filter("Laminas\Filter\Digits")
     * @Form\Validator("Laminas\Validator\Digits")
     */
    public $displayOrder = null;

    /**
     * @Form\Options({
     *     "label": "Active",
     *     "checked_value": "1",
     *     "unchecked_value": "0"
     * })
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"", "id":"isActive"})
     */
    public $isActive = null;
}
