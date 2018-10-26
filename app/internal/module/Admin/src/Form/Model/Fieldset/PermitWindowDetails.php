<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;

/**
 * @codeCoverageIgnore No methods
 */
class PermitWindowDetails
{
    use IdTrait;

    /**
     * @Form\Type("Hidden")
     */
    public $stockId = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"js-hidden","data-container-class":"js-hidden"})
     * @Form\Options({
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Name("compareStartDate")
     */
    public $compareStartDate = null;


    /**
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "internal.community_licence.form.start_date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Name("startDate")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $startDate = null;

    /**
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "internal.community_licence.form.end_date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {
     *          "has_time": false,
     *          "allow_empty": true,
     *          "compare_to": "startDate",
     *          "operator": "gte",
     *          "compare_to_label": "Start date",
     *          "messageTemplates": {
     *              "notGreaterThanOrEqual": "Window End Date must be later than Window Start Date"
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
