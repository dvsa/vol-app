<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("interim-details")
 */
class InterimDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $interimStatus = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"interimReason",
     *      "class":"long",
     *      "name":"interimReason",
     *      "required":false
     * })
     * @Form\Options({
     *     "label": "internal.interim.form.interim_reason",
     *     "label_attributes": {
     *         "class": "long"
     *     },
     *     "column-size": ""
     * })
     *
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $interimReason = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"interimStart"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_start",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $interimStart = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"interimEnd"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_end",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $interimEnd = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"short","id":"interimAuthVehicles","required":false})
     * @Form\Options({"label":"internal.interim.form.interim_auth_vehicles"})
     * @Form\Validator({"name":"Digits"})
     * @Form\Validator({"name": "GreaterThan", "options": {"min":1,"inclusive":true}})
     * @Form\Type("Text")
     */
    public $interimAuthVehicles = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"short","id":"interimAuthTrailers","required":false})
     * @Form\Options({"label":"internal.interim.form.interim_auth_trailers"})
     * @Form\Validator({"name":"Digits"})
     * @Form\Validator({"name": "GreaterThan", "options": {"min":0,"inclusive":true}})
     * @Form\Type("Text")
     */
    public $interimAuthTrailers = null;
}
