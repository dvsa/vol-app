<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-safety")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Safety
{
    /**
     * @Form\Name("licence")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\SafetyLicence")
     */
    public $licence;

    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\TableRequired")
     * @Form\Options({
     *     "label": "safety-inspection-providers.table.title",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     * @Form\Attributes({"id":"table"})
     */
    public $table;

    /**
     * @Form\Name("application")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\SafetyApplication")
     */
    public $application;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
