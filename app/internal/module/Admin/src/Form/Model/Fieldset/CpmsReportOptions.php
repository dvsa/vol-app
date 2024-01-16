<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("reportOptions")
 */
class CpmsReportOptions
{
    /**
     * @Form\Name("reportCode")
     * @Form\Type("Select")
     * @Form\Label("Report type")
     * @Form\Attributes({
     *      "id": "reportCode",
     * })
     * @Form\Options({"label":"Report type"})
     */
    public $reportCode = null;

    /**
     * @Form\Options({
     *     "label": "Start date",
     *     "short-label": "Start date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $startDate = null;

    /**
     * @Form\Options({
     *     "label": "End date",
     *     "short-label": "End date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {
     *          "has_time": false,
     *          "compare_to":"startDate",
     *          "operator":"gte",
     *          "compare_to_label":"Start date"
     *      }
     * })
     */
    public $endDate = null;
}
