<?php

namespace Olcs\Form\Model\Form\Vehicle\Fieldset;

use Zend\Form\Annotation as Form;

class VehicleTransferFormActions extends VehicleFormActions
{
    const LICENCE_FIELD = 'licence';

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "disable_inarray_validator": false,
     *     "render-container": false,
     *     "label": "licence.vehicle.transfer.select.licence.label",
     *     "label_attributes": {"class": "govuk-label govuk-!-padding-top-3 govuk-!-margin-bottom-4"}
     * })
     * @Form\Attributes({
     *     "id":"select-a-licence",
     *     "class":"govuk-!-margin-right-2 govuk-select"
     * })
     * @Form\Type("Select")
     */
    public $licence = null;
}
