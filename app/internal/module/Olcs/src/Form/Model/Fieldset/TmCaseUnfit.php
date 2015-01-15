<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class TmCaseUnfit extends CaseBase
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "MSI",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     */
    public $isMsi;

    /**
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $decisionDate = null;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date of notification (to TM)",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({
     *      "name": "Date",
     *      "options": {"format": "Y-m-d"}
     * },
     * {
     *      "name": "DateCompare",
     *      "options": {
     *          "compare_to":"decisionDate",
     *          "compare_to_label":"Date of decision",
     *          "operator": "gte"
     *      }
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $notifiedDate = null;

    /**
     * @Form\Options({
     *     "label": "Unfitness start date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $unfitnessStartDate = null;

    /**
     * @Form\Options({
     *     "label": "Unfitness end date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $unfitnessEndDate = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $decision = null;
}