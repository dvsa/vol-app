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
     * @Form\Type("DateSelect")
     */
    public $continuationDate = null;

    /**
     * @Form\Options({
     *     "label": "Review Date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": false
     * })
     * @Form\Type("DateSelect")
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
}
