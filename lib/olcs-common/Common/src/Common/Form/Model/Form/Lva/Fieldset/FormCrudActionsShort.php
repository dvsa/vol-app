<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class FormCrudActionsShort
{
    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"govuk-button",
     *     "aria-label": "Save and Continue",
     *     "id": "save"
     * })
     * @Form\Options({"label": "Save"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"govuk-button govuk-button--secondary",
     *     "id": "cancel"
     * })
     * @Form\Options({
     *     "label": "Cancel",
     *     "keepForReadOnly": true,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel;
}
