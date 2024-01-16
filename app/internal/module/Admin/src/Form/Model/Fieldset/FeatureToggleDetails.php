<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;

/**
 * @codeCoverageIgnore No methods
 */
class FeatureToggleDetails
{
    use IdTrait;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Name of toggle"})
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":32})
     */
    public $friendlyName = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"extra-long"})
     * @Form\Options({"label":"Config name (usually/ideally the FQDN of a handler)"})
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $configName = null;

    /**
     * @Form\Options({
     *     "label": "Toggle status",
     *     "category": "feature_toggle_status",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Type("DynamicRadio")
     * @Form\Validator({
     *      "name":"Laminas\Validator\NotEmpty"
     * })
     */
    public $status = null;
}
