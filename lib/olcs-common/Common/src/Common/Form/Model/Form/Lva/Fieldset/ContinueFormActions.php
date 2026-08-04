<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("continue-form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class ContinueFormActions
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label": "continue.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $continue;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     *     "id":"cancel",
     * })
     * @Form\Options({"label": "cancel.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel;
}
