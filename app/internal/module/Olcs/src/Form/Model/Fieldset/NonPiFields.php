<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class NonPiFields extends CaseBase
{
    /**
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":"agreedByTcDate"})
     * @Form\Options({
     *     "label": "Agreed by TC/DTC/HTRU/DHTRU date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $agreedByTcDate;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"hearingType","placeholder":""})
     * @Form\Options({
     *     "label": "Type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "non_pi_type"
     * })
     */
    public $hearingType = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateTimeSelect")
     * @Form\Attributes({"id":"hearingDate"})
     * @Form\Options({
     *     "label": "Meeting date",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "field": "hearingDate"
     * })
     * @Form\Filter("DateTimeSelectNullifier")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d H:i:s"})
     */
    public $hearingDate;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"venue","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Meeting venue",
     *     "service_name": "Common\Service\Data\Venue",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "other_option" : true
     * })
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $venue;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium","id":"venueOther", "required":false})
     * @Form\Options({"label":"Meeting venue other"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "venue",
     *          "context_values": {"other"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name":"Laminas\Validator\StringLength","options":{"max":255}}
     *          }
     *      }
     * })
     */
    public $venueOther;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "Number of witnesses"})
     * @Form\Filter("Digits")
     * @Form\Validator("Laminas\Validator\Digits")
     * @Form\Validator("Laminas\Validator\Between", options={"min":0,"max":99,"inclusive":true})
     */
    public $witnessCount;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Name of presiding staff member"})
     * @Form\Required(false)
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":255})
     */
    public $presidingStaffName = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"outcome","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "category": "non_pi_type_outcome",
     *     "disable_inarray_validator": false
     * })
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $outcome = null;
}
