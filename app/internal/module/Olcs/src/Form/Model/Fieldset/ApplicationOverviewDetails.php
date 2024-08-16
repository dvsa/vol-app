<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Application Overview Details fieldset
 */
class ApplicationOverviewDetails
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Lead Traffic Commissioner",
     *     "value_options": {
     *     },
     *     "empty_option": "Not set",
     *     "disable_inarray_validator": false,
     * })
     * @Form\Type("\Laminas\Form\Element\Select")
     * @Form\Required(false)
     */
    public $leadTcArea = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "Application received date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false,
     *     "max_year_delta": "+5",
     *     "min_year_delta": "-5"
     * })
     * @Form\Attributes({"required":false})
     * @Form\Filter({"name": "\Common\Filter\DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     */
    public $receivedDate = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "Target completion",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false,
     *     "max_year_delta": "+5",
     *     "min_year_delta": "-5"
     * })
     * @Form\Attributes({"required":false})
     * @Form\Filter({"name": "\Common\Filter\DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     */
    public $targetCompletionDate = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"overview.fieldset.check.welsh"})
     * @Form\Type("OlcsCheckbox")
     */
    public $translateToWelsh = null;

    /**
     * @Form\Options({
     *      "checked_value":"Y",
     *      "unchecked_value":"N",
     *      "label":"overview.fieldset.check.override-opposition-date"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $overrideOppositionDate = null;

    /**
     * @Form\Options({
     *      "checked_value":"Y",
     *      "unchecked_value":"N",
     *      "label":"overview.fieldset.check.application-referred-to-pi"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $applicationReferredToPi = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;
}
