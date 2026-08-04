<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lva-safety-providers-data")
 */
class SafetyProvidersData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "application_vehicle-safety_safety-sub-action.data.isExternal",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "value_options": {
     *         "N": "application_vehicle-safety_safety-sub-action.data.isExternal.option.no",
     *         "Y": "application_vehicle-safety_safety-sub-action.data.isExternal.option.yes"
     *     }
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $isExternal;
}
