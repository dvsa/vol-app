<?php

namespace Common\Form\Model\Form\Continuation;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post"})
 * @Form\Options({
 *     "formErrorsTitle":"continuations.checklist.form-errors-title",
 *     "formErrorsParagraph":"continuations.checklist.form-errors-paragraph"
 * })
 * @Form\Type("Common\Form\Form")
 */
class LicenceChecklist
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\LicenceChecklist")
     * @Form\Options({
     *     "hint": "continuations.checklist.form-hint",
     *     "hintClass": "form-hint",
     *     "label": "continuations.checklist.hidden.legend",
     *     "label_attributes": {"class": "govuk-visually-hidden"},
     *     "shouldWrap": true,
     *  })
     */
    public $data;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\LicenceChecklistConfirmation")
     * @Form\Options(
     *     {
     *          "label" : "continuations.checklist.confirmation.label",
     *     }
     * )
     */
    public $licenceChecklistConfirmation;
}
