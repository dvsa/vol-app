<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("TemplateEdit")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class TemplateEdit
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $format = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $description = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $jsonUrl = null;

    /**
     * Holds the rendered "Other versions:" sibling-pill HTML. Populated dynamically by
     * TemplateController::alterFormForEdit so admins can jump between this row's html, plain
     * and md siblings without going back to the list. VOL-7238.
     *
     * @Form\Attributes({"id":"templateSiblings"})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $templateSiblings = null;

    /**
     * Holds the markdown-only help hint ("rendered by GOV.UK Notify, no raw HTML…").
     * Populated dynamically; empty for html/plain rows. VOL-7238.
     *
     * @Form\Attributes({"id":"mdHint"})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $mdHint = null;

    /**
     * @Form\Type("TextArea")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "id": "source",
     *     "placeholder": "Add markup here",
     *     "class": "extra-long",
     * })
     * @Form\Options({
     *     "label":"Template Markup",
     * })
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":65535})
     */
    public $source;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\FormSaveCancelPreview")
     */
    public $formActions = null;
}
