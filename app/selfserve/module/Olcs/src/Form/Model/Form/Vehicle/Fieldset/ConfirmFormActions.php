<?php

namespace Olcs\Form\Model\Form\Vehicle\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class ConfirmFormActions
{
    /**
     * @Form\Options({
     *     "label": "Next",
     * })
     * @Form\Attributes({
     *     "id": "next",
     *     "title": "licence.vehicle.generic.button.next.title",
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button"
     * })
     * @Form\Type("Button")
     */
    public $formActions = null;
}
