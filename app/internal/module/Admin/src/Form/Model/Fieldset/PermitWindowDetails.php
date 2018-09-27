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
    public $parentId = null;


    /**
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "internal.community_licence.form.start_date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({
     *      "name": "Dvsa\Olcs\Transfer\Validators\DateInFuture",
     *      "options": {
     *          "include_today": true,
     *          "use_time": false
     *      }
     * })
     */
    public $startDate = null;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "internal.community_licence.form.end_date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
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
     *          "compare_to":"startDate",
     *          "operator":"gte",
     *          "compare_to_label":"Start date"
     *      }
     * })
     * @Form\Validator({
     *      "name": "Dvsa\Olcs\Transfer\Validators\DateInFuture",
     *      "options": {
     *          "include_today": true,
     *          "use_time": false,
     *          "allow_empty": true,
     *          "error-message": "Window End Date must be later than Window Start Date"
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
     * @Form\Type("Text")
     * @Form\Required(false)
     */
    public $daysForPayment = null;
}
