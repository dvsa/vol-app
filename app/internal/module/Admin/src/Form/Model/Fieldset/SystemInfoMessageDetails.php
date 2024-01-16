<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("system-info-message-details")
 */
class SystemInfoMessageDetails
{
    use IdTrait;

    /**
     * @Form\Type("Laminas\Form\Element\Radio")
     * @form\Required(true)
     * @Form\Options({
     *     "label": "Display to:",
     *     "value_options": {
     *          "Y": "Internal",
     *          "N": "Self Serve"
     *      },
     * })
     * @Form\Attributes({
     *     "value":"Y",
     *     "required": true,
     * })
     * @form\Filter({"name":"StripTags"})
     * @form\Validator({
     *     "name": "InArray",
     *     "options": {"haystack":{"Y", "N"},
     * }})
     */
    public $isInternal;

    /**
     * @Form\Type("TextArea")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "id": "description",
     *     "placeholder": "write information message",
     *     "class": "extra-long",
     * })
     * @Form\Options({
     *     "label":"Message",
     * })
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":1024})
     */
    public $description;

    /**
     * @Form\Type("DateTimeSelect")
     * @form\Required(true)
     * @Form\Options({
     *     "label": "Start:",
     *      "create_empty_option": true,
     *      "max_year_delta": "+1",
     *      "min_year_delta": "0",
     *     "pattern": "d MMMM y '</fieldset><fieldset><div class=""field""><label>Time</label>'HH:mm:ss'</div>'"
     * })
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({
     *     "name": "Date",
     *     "options": {
     *         "format": "Y-m-d H:i:s",
     *         "messages": {
     *             "dateInvalidDate": "datetime.compare.validation.message.invalid"
     *         }
     *     }
     * })
     */
    public $startDate = null;

    /**
     * @Form\Type("DateTimeSelect")
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "End:",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "0",
     *     "pattern": "d MMMM y '</fieldset><fieldset><div class=""field""><label>Time</label>'HH:mm:ss'</div>'"
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
     *                      "compare_to_label": "Start"
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
