<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\Type("\Zend\Form\Element\Select")
     * @Form\Required(false)
     */
    public $leadTcArea = null;

    /**
     * @Form\Options({
     *     "label": "Application received date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false,
     *     "max_year_delta": "+5",
     *     "min_year_delta": "-5"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name": "Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $receivedDate = null;

    /**
     * @Form\Options({
     *     "label": "Target completion",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false,
     *     "max_year_delta": "+5",
     *     "min_year_delta": "-5"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name": "Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
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
