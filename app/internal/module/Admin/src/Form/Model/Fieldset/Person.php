<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("person")
 */
class Person
{
    /**
     * @Form\Attributes({"id":"title","placeholder":"","class":"small"})
     * @Form\Options({
     *     "label": "Title",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "person_title"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $title = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"First name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Last name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"id":"birthDate"})
     * @Form\Options({
     *     "label": "Birth date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $birthDate = null;
}
