<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class NonPiFields extends CaseBase
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"agreedByTcDate"})
     * @Form\Options({
     *     "label": "Agreed by TC/DTC/HTRU/DHTRU date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $agreedByTcDate;

    /**
     * @Form\Attributes({"id":"hearingType","placeholder":""})
     * @Form\Options({
     *     "label": "Type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "non_pi_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $hearingType = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"hearingDate"})
     * @Form\Options({
     *     "label": "Meeting date",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "pattern": "d MMMM y '</fieldset><fieldset><div class=""field""><label for=""hearingDate"">Meeting time</label>'HH:mm:ss'</div>'",
     *     "field": "hearingDate"
     * })
     * @Form\Type("DateTimeSelect")
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d H:i:s"}})
     */
    public $hearingDate;

    /**
     * @Form\Attributes({"id":"venue","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Meeting venue",
     *     "service_name": "Common\Service\Data\Venue",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "other_option" : true
     * })
     *
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $venue;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"medium","id":"venueOther", "required":false})
     * @Form\Options({"label":"Meeting venue other"})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "venue",
     *          "context_values": {"other"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Zend\Validator\NotEmpty"},
     *              {"name":"Zend\Validator\StringLength","options":{"max":255}}
     *          }
     *      }
     * })
     */
    public $venueOther;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "Number of witnesses"})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Digits"})
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Validator({"name":"Zend\Validator\Between","options":{"min":0,"max":99,"inclusive":true}})
     */
    public $witnessCount;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Name of presiding staff member"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":0,"max":255}})
     */
    public $presidingStaffName = null;

    /**
     * @Form\Attributes({"id":"outcome","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "category": "non_pi_type_outcome",
     *     "disable_inarray_validator": false
     * })
     *
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $outcome = null;
}
