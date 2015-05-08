<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Licence Overview Details fieldset
 */
class LicenceOverviewDetails
{
    /**
     * @Form\Options({
     *     "label": "Continuation Date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false,
     *     "max_year_delta": "+10"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $continuationDate = null;

    /**
     * @Form\Options({
     *     "label": "Review Date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false,
     *     "max_year_delta": "+10"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
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
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $leadTcArea = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"overview.fieldset.check.welsh"})
     * @Form\Type("OlcsCheckbox")
     */
    public $translateToWelsh = null;
}
