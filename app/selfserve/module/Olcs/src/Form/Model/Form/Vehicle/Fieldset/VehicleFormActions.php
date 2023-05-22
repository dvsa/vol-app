<?php

namespace Olcs\Form\Model\Form\Vehicle\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class VehicleFormActions
{
    /**
     * @Form\Attributes({
     *     "id": "action-button",
     *     "type":"submit",
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $action = null;

    /**
     * @Form\Attributes({
     *     "id": "cancel-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary"
     * })
     * @Form\Options({"label": "cancel.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
