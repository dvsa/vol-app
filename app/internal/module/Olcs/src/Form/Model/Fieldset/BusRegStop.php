<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-reg-stop-fields")
 */
class BusRegStop extends BusRegDetails
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Use all bus stops",
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
    public $useAllStops;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Manoeuvres",
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
    public $hasManoeuvre = 0;

    /**
     * @Form\Attributes({
     *      "id":"manoeuvreDetail",
     *      "class":"extra-long",
     *      "name":"manoeuvreDetail"
     * })
     * @Form\Options({
     *     "label": "Manoeuvres comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength",
     *      options={
     *          "max":255
     *      }
     * )
     */
    public $manoeuvreDetail = null;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Need new bus stops",
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
    public $needNewStop = 0;

    /**
     * @Form\Attributes({
     *      "id":"newStopDetail",
     *      "class":"extra-long",
     *      "name":"newStopDetail"
     * })
     * @Form\Options({
     *     "label": "Need new bus stops comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength",
     *      options={
     *          "max":255
     *      }
     * )
     */
    public $newStopDetail = null;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "No fixed stopping points",
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
    public $hasNotFixedStop = 0;

    /**
     * @Form\Attributes({
     *      "id":"notFixedStopDetail",
     *      "class":"extra-long",
     *      "name":"notFixedStopDetail"
     * })
     * @Form\Options({
     *     "label": "No fixed stopping points comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength",
     *      options={
     *          "max":255
     *      }
     * )
     */
    public $notFixedStopDetail = null;

    /**
     * @Form\Attributes({
     *      "id":"subsidised",
     *      "placeholder":"",
     *      "class":"small",
     *      "value":"bs_no"
     * })
     * @Form\Options({
     *     "label": "Supported by subsidies",
     *     "disable_inarray_validator": false,
     *     "category": "bus_subsidy"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $subsidised = null;

    /**
     * @Form\Attributes({
     *      "id":"subsidyDetail",
     *      "class":"extra-long",
     *      "name":"subsidyDetail"
     * })
     * @Form\Options({
     *     "label": "Supported by subsidies comments",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength",
     *      options={
     *          "max":255
     *      }
     * )
     */
    public $subsidyDetail = null;
}
