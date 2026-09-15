<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class CreateAccountButtons
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button"
     * })
     * @Form\Options({"label": "user-registration.form-actions.create-account.label"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit;
}
