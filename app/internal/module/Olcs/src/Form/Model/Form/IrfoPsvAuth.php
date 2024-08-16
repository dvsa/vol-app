<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("IrfoPsvAuth")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrfoPsvAuth
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrfoPsvAuth")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrfoPsvAuthFormActions")
     */
    public $formActions = null;
}
