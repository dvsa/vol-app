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
     * @Form\Attributes({"id":"type","placeholder":""})
     * @Form\Options({
     *     "label": "Type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a case type",
     *     "category": "non_pi_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $hearingType = null;

    /**
     * @Form\Attributes({"id":"hearingDate"})
     * @Form\Options({
     *     "label": "Meeting date",
     *     "create_empty_option": true,
     *     "max_year": 2016,
     *     "render_delimiters": true,
     *     "pattern": "d MMMM y '</div><div class=""field""><label for=hearingDate>Meeting time</label>'HH:mm:ss",
     *     "category": "pi_hearing",
     *     "field": "hearingDate"
     * })
     * @Form\Type("DateTimeSelect")
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d H:i:s"}})
     */
    public $hearingDate;

    /**
     * @Form\Attributes({"id":"piVenue","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Meeting venue",
     *     "service_name": "Common\Service\Data\PiVenue",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
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
     * @Form\Validator({"name":"Digits"})
     */
    public $witnessCount;

    /**
     * @Form\Attributes({"id":"presidingTc","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/TR/DTR",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $presidingTc;
}
