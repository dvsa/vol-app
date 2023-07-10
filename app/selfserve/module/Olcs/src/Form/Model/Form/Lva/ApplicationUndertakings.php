<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-undertakings")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class ApplicationUndertakings
{
    /**
     * @Form\Name("declarationsAndUndertakings")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\ApplicationDeclarationsAndUndertakings")
     */
    public $declarationsAndUndertakings = null;

    /**
     * @Form\Name("interim")
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\Interim")
     * @Form\Options({
     *     "label": "interim.application.undertakings.form.checkbox.label"})
     */
    public $interim = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Form\Lva\Fieldset\FormActionsUndertakings")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions = null;
}
