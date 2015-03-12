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
     *      "short-label": "short-label-tol-operator-type",
     *      "fieldset-attributes": {
     *          "id": "operator-type",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-type",
     *      "label": "application_type-of-licence_operator-type.data.goodsOrPsv",
     *      "value_options":{
     *          "lcat_gv":"Goods",
     *          "lcat_psv":"PSV"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $goodsOrPsv = null;
}
