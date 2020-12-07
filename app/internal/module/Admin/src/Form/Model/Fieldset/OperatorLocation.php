<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("Laminas\Form\Fieldset")
 */
class OperatorLocation
{
    /**
     * @Form\Name("niFlag")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "id": "operator-location",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-location",
     *      "label": "Operator location",
     *      "value_options":{
     *          "N":"Great Britain",
     *          "Y":"Northern Ireland"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $niFlag = null;
}
