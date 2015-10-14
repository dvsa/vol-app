<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("bus-request-map-fields")
 */
class BusRequestMap
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Scale",
     *      "value_options":{
     *          "small":"1:50000",
     *          "large":"1:10000"
     *      },
     * })
     * @Form\Attributes({"value": "small"})
     */
    public $scale;
}
