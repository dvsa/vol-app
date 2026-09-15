<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class CaseFormActions
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button"
     * })
     * @Form\Options({"label": "Save"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     *     "id":"cancel",
     * })
     * @Form\Options({"label": "Cancel"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel;
}
