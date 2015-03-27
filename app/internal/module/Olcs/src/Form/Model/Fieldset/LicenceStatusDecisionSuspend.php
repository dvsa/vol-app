<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-decision-suspend")
 */
class LicenceStatusDecisionSuspend
{
    /**
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "licence-status.suspension.from",
     *      "create_empty_option": true,
     *      "min_year_delta": "-5",
     * })
     * @Form\Attributes({"required":false})
     */
    public $suspendFrom = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "licence-status.suspension.to",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "-5"
     * })
     */
    public $suspendTo = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
