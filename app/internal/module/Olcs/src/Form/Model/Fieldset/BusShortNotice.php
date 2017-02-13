<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-short-notice-fields")
 */
class BusShortNotice extends Base
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Bank holiday change",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $bankHolidayChange;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Unforseen circumstances",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $unforseenChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "Unforeseen circumstances comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $unforseenDetail;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Timetable change (< 10 minutes +/- originally registered service)",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $timetableChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "Timetable change (< 10 minutes +/- originally registered service) comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $timetableDetail;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Service to replace substantially similar service run by another operator",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $replacementChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "Service to replace substantially similar service run by another operator comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $replacementDetail;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Service not available to/used by general public",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $notAvailableChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "Service not available to/used by general public comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $notAvailableDetail;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Cater for additional demands for special occasions / events",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $specialOccasionChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "Cater for additional demands for special occasions / events comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $specialOccasionDetail;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Change to match changed connecting rail/ferry/air service",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $connectionChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "Change to match changed connecting rail/ferry/air service comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $connectionDetail;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Change <= 14 days to match local public holiday or widely observed local holiday",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $holidayChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "Change <= 14 days to match local public holiday or widely observed local holiday comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $holidayDetail;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "To comply with TRC",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $trcChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "To comply with TRC comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $trcDetail;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Police / Traffic Authority requires change for road safety reasons",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $policeChange;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"extra-long"
     * })
     * @Form\Options({
     *     "label": "Police / Traffic Authority requires change for road safety reasons comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":255
     *      }
     * })
     */
    public $policeDetail;
}
