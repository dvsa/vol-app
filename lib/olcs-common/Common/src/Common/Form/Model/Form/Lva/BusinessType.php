<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\VersionTrait;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("application_your-business_business-type")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class BusinessType
{
    use VersionTrait;

    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\BusinessType")
     */
    public $data;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
