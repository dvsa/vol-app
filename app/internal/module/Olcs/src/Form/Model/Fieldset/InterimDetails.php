<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

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
     *     }
     * })
     *
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
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
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
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
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $interimEnd = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"short","id":"interimAuthHgvVehicles"})
     * @Form\Options({"label":"internal.interim.form.interim_auth_hgv_vehicles"})
     * @Form\Type("Text")
     * @Form\Validator("Digits")
     */
    public $interimAuthHgvVehicles = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"short","id":"interimAuthLgvVehicles"})
     * @Form\Options({"label":"internal.interim.form.interim_auth_lgv_vehicles"})
     * @Form\Type("Text")
     * @Form\Validator("Digits")
     */
    public $interimAuthLgvVehicles = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"short","id":"interimAuthTrailers"})
     * @Form\Options({"label":"internal.interim.form.interim_auth_trailers"})
     * @Form\Type("Text")
     * @Form\Validator("Digits")
     */
    public $interimAuthTrailers = null;
}
