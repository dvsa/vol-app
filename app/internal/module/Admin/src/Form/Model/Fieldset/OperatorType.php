<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 */
class OperatorType
{
    /**
     * @Form\Name("goodsOrPsv")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "id": "operator-type",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-type",
     *      "label": "Operator type",
     *      "value_options":{
     *          "lcat_gv":"Goods",
     *          "lcat_psv":"PSV"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $goodsOrPsv = null;
}
