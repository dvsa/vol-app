<?php

namespace Common\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("case")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class CaseForm
{
    /**
     * @Form\Name("submissionSections")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SubmissionSections")
     * @Form\Options({"label":"Select one or more categories"})
     */
    public $submissionSections;

    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Fields")
     */
    public $fields;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CaseFormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
