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
     * @Form\Required(false)
     * @Form\Attributes({
     *      "id":"interimReason",
     *      "class":"long",
     *      "name":"interimReason"
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
     * @Form\Required(false)
     * @Form\Attributes({"id":"interimStart"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_start",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "min_year_delta": "-5",
     *     "max_year_delta": "+5"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $interimStart = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"interimEnd"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_end",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "min_year_delta": "-5",
     *     "max_year_delta": "+5"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $interimEnd = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"short","id":"interimAuthVehicles"})
     * @Form\Options({"label":"internal.interim.form.interim_auth_vehicles"})
     * @Form\Type("Text")
     */
    public $interimAuthVehicles = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"short","id":"interimAuthTrailers"})
     * @Form\Options({"label":"internal.interim.form.interim_auth_trailers"})
     * @Form\Type("Text")
     */
    public $interimAuthTrailers = null;
}
