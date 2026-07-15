<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-psv-discs-request")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class PsvDiscsRequest
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\PsvDiscsRequestData")
     */
    public $data;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
