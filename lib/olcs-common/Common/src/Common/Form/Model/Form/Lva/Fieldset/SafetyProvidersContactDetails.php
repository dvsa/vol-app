<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lva-safety-providers-contact-details")
 * @Form\Options({})
 */
class SafetyProvidersContactDetails
{
    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({
     *     "label":"application_vehicle-safety_safety-sub-action.data.fao",
     *     "label_attributes": {
     *         "aria-label": "Enter the name of the contractor or employee"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1,"max":90})
     */
    public $fao;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;
}
