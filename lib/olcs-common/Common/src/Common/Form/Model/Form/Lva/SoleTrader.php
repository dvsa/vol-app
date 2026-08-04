<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-sole-trader")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class SoleTrader
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\SoleTrader")
     */
    public $data;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActionsPerson")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
