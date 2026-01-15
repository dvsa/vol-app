<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterIssueType
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Code"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"short", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":10})
     */
    public $code = null;

    /**
     * @Form\Options({"label": "Name"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    public $name = null;

    /**
     * @Form\Options({"label": "Description"})
     * @Form\Required(false)
     * @Form\Type("Textarea")
     * @Form\Attributes({"class":"extra-long", "rows": 3})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $description = null;

    /**
     * @Form\Options({"label": "Display Order"})
     * @Form\Required(true)
     * @Form\Type("Number")
     * @Form\Attributes({"class":"short", "required": true})
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
