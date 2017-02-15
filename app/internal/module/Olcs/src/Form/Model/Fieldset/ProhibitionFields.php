<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class ProhibitionFields extends Base
{
    /**
     * @Form\Attributes({"id":"prohibitionDate"})
     * @Form\Options({
     *     "label": "Prohibition date",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $prohibitionDate = null;

    /**
     * @Form\Attributes({"class":"","id":""})
     * @Form\Options({"label":"Vehicle registration mark"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Common\Filter\Vrm"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Vrm"})
     */
    public $vrm = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Trailer"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isTrailer = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "prohibition_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $prohibitionType = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"required":false, "id":"clearedDate"})
     * @Form\Options({
     *     "label": "Date cleared",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("DateSelect")
     * @Form\AllowEmpty(true)
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "prohibitionDate",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"prohibitionDate",
     *                      "compare_to_label":"Prohibition Date",
     *                      "operator": "gte",
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $clearedDate = null;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Location prohibition issued"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":255}})
     */
    public $imposedAt = null;
}
