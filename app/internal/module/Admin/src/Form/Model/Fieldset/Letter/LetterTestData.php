<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterTestData
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Name"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $name = null;

    /**
     * @Form\Options({
     *     "label": "JSON Data",
     *     "label_attributes": {
     *         "class": ""
     *     },
     *     "hint": "Enter valid JSON data for testing"
     * })
     * @Form\Required(true)
     * @Form\Type("Textarea")
     * @Form\Attributes({"class":"extra-long", "rows": 10})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\IsJsonString")
     */
    public $json = null;
}