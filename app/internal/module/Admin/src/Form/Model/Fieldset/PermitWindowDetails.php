<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Zend\Form\Annotation as Form;

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
     * @Form\Attributes({"id":"emissionsCategory"})
     * @form\Required(true)
     * @Form\Options({
     *     "label": "Emissions category question",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "emissions_category"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $emissionsCategory = null;

    /**
     * @Form\Type("DateTimeSelect")
     * @form\Required(true)
     * @Form\Attributes({"id":"startDate"})
     * @Form\Options({
     *     "label": "internal.community_licence.form.start_date",
     *     "create_empty_option": true,
     *     "max_year_delta": "+1",
     *     "min_year_delta": "0",
     *     "pattern": "d MMMM y '</fieldset><fieldset><div class=""field""><label for=""startDate"">Time</label>'HH:mm:ss'</div>'",
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
     *     "pattern": "d MMMM y '</fieldset><fieldset><div class=""field""><label for=""endDate"">Time</label>'HH:mm:ss'</div>'",
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

    /**
     * @Form\Name("daysForPayment")
     * @Form\Attributes({"id": "daysForPayment"})
     * @Form\Options({
     *      "label": "Days for Payment",
     *      "required": false
     * })
     * @Form\Type("Number")
     * @Form\Required(true)
     */
    public $daysForPayment = null;
}
