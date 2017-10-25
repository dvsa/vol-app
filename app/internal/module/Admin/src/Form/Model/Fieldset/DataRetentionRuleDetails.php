<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Class DataRetentionExportOptions
 */
class DataRetentionRuleDetails
{
    /**
     * @Form\Type("number")
     * @Form\Options({"label":"Rule id","readonly":true})
     * @Form\Attributes({"readonly":"true"})
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Description"})
     * @Form\Required(true)
     * @Form\Type("textarea")
     * @Form\Attributes({"class":"extra-long"})
     */
    public $description = null;

    /**
     * @Form\Options({"label": "Retention period"})
     * @Form\Required(true)
     * @Form\Type("number")
     * @Form\Attributes({"class":"small"})
     */
    public $retentionPeriod = null;

    /**
     * @Form\Options({"label": "Max data set"})
     * @Form\Required(true)
     * @Form\Type("number")
     * @Form\Attributes({"class":"small"})
     */
    public $maxDataSet = null;

    /**
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "isEnabled",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *     "value":"N",
     *     "class":"inline"
     * })
     */
    public $isEnabled = null;

    /**
     * @Form\Type("select")
     * @Form\Options({
     *     "label":"Action type",
     *     "value_options":{
     *          "Automate":"Automate",
     *          "Review":"Review"
     *      }
     * })
     */
    public $actionType = null;
}
