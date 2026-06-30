<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-vehicles-size")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class VehiclesDeclarationsSize
{
    /**
     * @Form\Name("version")
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Name("psvVehicleSize")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\PsvVehiclesSize")
     */
    public $psvVehicleSize;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
