<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
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
     *     "label": "application_your-business_people-sub-action-formTitle",
     *     "label_attributes": {"class": "form-element__question"},
     *     "category":"person_title",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $title;

    /**
     * @Form\Attributes({"class":"long","id":"forename"})
     * @Form\Options({
     *     "label":"application_your-business_people-sub-action-formFirstName",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "person_forename-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    public $forename;

    /**
     * @Form\Attributes({"class":"long","id":"familyname"})
     * @Form\Options({
     *    "label":"application_your-business_people-sub-action-formSurname",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "person_familyName-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    public $familyName;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *    "label":"application_your-business_people-sub-action-formOtherNames",
     *     "label_attributes": {"class": "form-element__question"}
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    public $otherName;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"application_your-business_people-sub-action-formPosition"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":45})
     */
    public $position;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "dob",
     *     "create_empty_option": false,
     *     "render_delimiters": true,
     *     "error-message": "person_birthDate-error",
     *     "fieldset-attributes": {"id":"dob_day"}
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelect", options={"null_on_empty":true})
     * @Form\Validator("NotEmpty", options={"array"})
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $birthDate;
}
