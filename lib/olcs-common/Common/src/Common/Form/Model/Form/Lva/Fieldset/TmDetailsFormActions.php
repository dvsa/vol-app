<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("tm-details-form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class TmDetailsFormActions
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label": "tm-details-submit.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     * })
     * @Form\Options({"label": "tm-details-save.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save;
}
