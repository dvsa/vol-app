<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("licenceChecklistConfirmationNo")
 */
class LicenceChecklistConfirmationNo
{
    /**
     * @Form\Attributes({"value": "markup-continuation-licence-checklist-confirmation-no"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $checklistDeclineText;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     * })
     * @Form\Options({"label": "continuations.checklist.confirmation.no-button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionLink")
     */
    public $backToLicence;
}
