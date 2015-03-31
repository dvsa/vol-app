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
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "licence-status.curtailment.from",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "-5"
     * })
     * @Form\Attributes({"required":false})
     */
    public $curtailFrom = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Required(false)
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "curtailTo",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name":"Date", "options":{"format":"Y-m-d"}},
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"curtailFrom",
     *                      "operator":"gte",
     *                      "compare_to_label":"Curtail from"
     *                  }
     *              }
     *          }
     *      }
     * })
     * @Form\Options({
     *     "label": "licence-status.curtailment.to",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "-5"
     * })
     */
    public $curtailTo = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
