<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("BusRegVariationReason")
 */
class BusRegVariationReason
{
    /**
     * @Form\Attributes({"id":"variationReasons","placeholder":"","multiple":"multiple",
     *     "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "Variation reason",
     *     "category": "bus_reg_var_reason"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $variationReasons = null;
}
