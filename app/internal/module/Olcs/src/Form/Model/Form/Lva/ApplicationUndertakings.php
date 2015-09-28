<?php

namespace Olcs\Form\Model\Form\Lva;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-declarations")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class ApplicationUndertakings
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Name("declarations")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\DeclarationsInternal")
     */
    public $declarations = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
