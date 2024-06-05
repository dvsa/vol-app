<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("permitWindowDetails")
 */
class PermitWindowDetails
{
    use IdTrait;

    /**
     * @Form\Type("Hidden")
     */
    public $stockId = null;

    /**
     * @Form\Type("DateTimeSelect")
     * @form\Required(true)
     * @Form\Attributes({"id":"startDate"})
     * @Form\Options({
     *     "label": "internal.community_licence.form.start_date",
     *     "create_empty_option": true,
     *     "max_year_delta": "+1",
     *     "min_year_delta": "0",
     *     "display_every_minute": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "startDate",
     *          "context_values": {"-- ::00"},
     *          "context_truth": false,
     *          "allow_empty" : false,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {
     *                  "name": "Date",
     *                  "options": {
     *                      "format": "Y-m-d H:i:s",
     *                      "messages": {
     *                          "dateInvalidDate": "datetime.compare.validation.message.invalid"
     *                      }
     *                  },
     *                  "break_chain_on_failure": true,
     *              },
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "has_time": true,
     *                      "compare_to":"endDate",
     *                      "operator":"lt",
     *                      "compare_to_label": "End date"
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $startDate = null;

    /**
     * @Form\Type("DateTimeSelect")
     * @Form\Required(true)
     * @Form\Attributes({"id":"endDate"})
     * @Form\Options({
     *     "label": "internal.community_licence.form.end_date",
     *     "create_empty_option": true,
     *     "max_year_delta": "+10",
     *     "min_year_delta": "0",
     *     "display_every_minute": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "endDate",
     *          "context_values": {"-- ::00"},
     *          "context_truth": false,
     *          "allow_empty" : false,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {
     *                  "name": "Date",
     *                  "options": {
     *                      "format": "Y-m-d H:i:s",
     *                      "messages": {
     *                          "dateInvalidDate": "datetime.compare.validation.message.invalid"
     *                      }
     *                  },
     *                  "break_chain_on_failure": true,
     *              },
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "has_time": true,
     *                      "compare_to":"startDate",
     *                      "operator":"gt",
     *                      "compare_to_label": "Start date"
     *                  }
     *              },
     *              {
     *                  "name": "DateInFuture",
     *              }
     *          }
     *      }
     * })
     */
    public $endDate = null;
}
