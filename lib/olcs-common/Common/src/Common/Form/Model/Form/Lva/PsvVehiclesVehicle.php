<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("application_vehicle-safety_vehicle-psv-sub-action")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class PsvVehiclesVehicle
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\PsvVehicleData")
     */
    public $data;

    /**
     * @Form\Name("licence-vehicle")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\LicenceVehicle")
     */
    public $licenceVehicle;

    /**
     * @Form\Name("vehicle-history-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $vehicleHistoryTable;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
