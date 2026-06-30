<?php

namespace Common\Form\Model\Form\Licence\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Class Person
 *
 * @package Common\Form\Model\Form\Licence\Fieldset
 *
 *
 */
class Person
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"id":"title","placeholder":""})
     * @Form\Options({
     *     "empty_option": "Please Select",
     *     "label": "Title",
     *     "label_attributes": {"class": "form-element__question"},
     *     "category":"person_title",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     */
    public $title;

    /**
     * @Form\Attributes({"class":"long","id":"forename"})
     * @Form\Options({
     *     "label":"First name",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "Enter first name"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    public $forename;

    /**
     * @Form\Attributes({"class":"long","id":"familyname"})
     * @Form\Options({
     *    "label":"Last name",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "Enter last name"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    public $familyName;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *    "label":"Other names (optional)",
     *     "label_attributes": {"class": "form-element__question"}
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    public $otherName;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date of Birth",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "error-message": "Enter date of birth"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $birthDate;
}
