<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class SoleTrader
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
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "empty_option": "Please Select",
     *     "label": "application_your-business_people-sub-action-formTitle",
     *     "category":"person_title",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $title;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "application_your-business_people-sub-action-formFirstName",
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $forename;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "application_your-business_people-sub-action-formSurname",
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $familyName;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label":"application_your-business_people-sub-action-formOtherNames",
     *     "hint":"application_your-business_people-sub-action-formOtherNames-hint"
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $otherName;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "application_your-business_people-sub-action-formDateOfBirth",
     *     "create_empty_option": false,
     *     "render_delimiters": true
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $birthDate;
}
