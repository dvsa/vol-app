<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("html-editor")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class HtmlEditor
{
    /**
     * @Form\Name("sections")
     * @Form\Options({"label":"Select sections to include"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\HtmlEditorSections")
     */
    public $sections = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\HtmlEditorFormActions")
     */
    public $formActions = null;
}
