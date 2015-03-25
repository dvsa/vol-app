<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-decision-curtail")
 */
class LicenceStatusDecisionCurtail
{
    /**
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\DateLessThanOrEqual", "options": {"token":"curtailTo"}})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "licence-status.curtailment.from",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10"
     * })
     */
    public $curtailFrom = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "licence-status.curtailment.to",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "-5",
     * })
     */
    public $curtailTo = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionCurtailFormActions")
     */
    public $formActions = null;
}
