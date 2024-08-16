<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("erru_penalty_fields")
 */
class ErruPenalty extends CaseBase
{
    /**
     * @Form\Options({
     *     "label": "Penalty type",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\SiPenaltyType",
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $siPenaltyType;

    /**
     * @Form\Attributes({"id":"startDate"})
     * @Form\Options({
     *     "label": "Start date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     */
    public $startDate;

    /**
     * @Form\Attributes({"id":"endDate"})
     * @Form\Options({
     *     "label": "End date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     */
    public $endDate;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Imposed",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     */
    public $imposed;

    /**
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({"label":"Reason not imposed"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":500})
     */
    public $reasonNotImposed;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $si;
}
