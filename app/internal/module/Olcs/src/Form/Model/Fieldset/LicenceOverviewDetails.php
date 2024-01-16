<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Licence Overview Details fieldset
 */
class LicenceOverviewDetails
{
    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "Continuation date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false,
     *     "max_year_delta": "+10"
     * })
     * @Form\Attributes({"required":false})
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $continuationDate = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Review Date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false,
     *     "max_year_delta": "+10"
     * })
     * @Form\Attributes({"required":false})
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $reviewDate = null;

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
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"overview.fieldset.check.welsh"})
     * @Form\Type("OlcsCheckbox")
     */
    public $translateToWelsh = null;
}
