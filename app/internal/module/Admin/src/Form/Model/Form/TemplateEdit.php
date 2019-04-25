<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("TemplateEdit")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class TemplateEdit
{
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
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength", "options":{"max":65535}})
     */
    public $source;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActionsShort")
     */
    public $formActions = null;
}
