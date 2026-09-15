<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-safety-providers")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class SafetyProviders
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\SafetyProvidersData")
     */
    public $data;

    /**
     * @Form\Name("contactDetails")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\SafetyProvidersContactDetails")
     */
    public $contactDetails;

    /**
     * @Form\Name("address")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     * @Form\Options({"label":"application_vehicle-safety_safety-sub-action.address.label"})
     */
    public $address;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
