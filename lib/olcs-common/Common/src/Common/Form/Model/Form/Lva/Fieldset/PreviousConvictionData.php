<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class PreviousConvictionData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "empty_option": "Please Select",
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formTitle",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "previousConvictionData_title-error",
     *     "category":"person_title",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $title;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formFirstName",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "previousConvictionData_forename-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $forename;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formLastName",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "previousConvictionData_familyName-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $familyName;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob","required":false})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formDateOfConviction",
     *     "label_attributes": {"class": "form-element__question"},
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "error-message": "previousConvictionData_convictionDate-error"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $convictionDate;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formOffence",
     *     "label_attributes": {"class": "form-element__question"}
     * })
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $categoryText;

    /**
     * @Form\Attributes({"id":"","class":"long"})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formOffenceDetails",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "previousConvictionData_notes-error",
     *     "hint": "selfserve-app-subSection-previous-history-criminal-conviction-formOffenceDetaisHelpBlock",
     *     "hint-position": "above"
     * })
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $notes;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formNameOfCourt",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "previousConvictionData_courtFpn-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $courtFpn;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formPenalty",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "previousConvictionData_penalty-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $penalty;
}
