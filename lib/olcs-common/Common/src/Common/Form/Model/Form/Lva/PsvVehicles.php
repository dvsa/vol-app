<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("application_vehicle-safety_vehicle-psv")
 * @Form\Attributes({"method":"post","class":"table__form"})
 * @Form\Type("Common\Form\Form")
 */
class PsvVehicles
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\VehiclesPsvData")
     */
    public $data;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\PsvVehicles")
     */
    public $vehicles;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\ShareInfo")
     */
    public $shareInfo;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
