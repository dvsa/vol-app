<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("licenceChecklistConfirmationYes")
 */
class LicenceChecklistConfirmationYes
{
    /**
     * @Form\Attributes({"value": "markup-continuation-licence-checklist-confirmation-yes"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $checklistConfirmText;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label":"continuations.checklist.confirmation.yes-button"})
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $submit;
}
