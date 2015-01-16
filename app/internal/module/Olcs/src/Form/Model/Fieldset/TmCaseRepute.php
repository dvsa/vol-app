<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class TmCaseRepute extends CaseBase
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
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {"compare_to": "decisionDate", "operator":"gte", "compare_to_label": "Date of decision"}
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $notifiedDate = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({"label":"Reason why loss of good repute is disproportionate response"})
     * @Form\Type("Textarea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":500}})
     */
    public $reputeNotLostReason = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $decision = null;
}
