<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     *     "disable_inarray_validator": false,
     *     "help-block": "Use CTRL to select multiple",
     *     "service_name": "Common\Service\Data\VariationReason",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $variationReasons = null;
}
