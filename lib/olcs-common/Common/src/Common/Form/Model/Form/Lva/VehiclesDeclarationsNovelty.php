<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-vehicles-declarations-novelty")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class VehiclesDeclarationsNovelty
{
    /**
     * @Form\Name("version")
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Name("limousinesNoveltyVehicles")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\VehiclesDeclarationsLimousinesNoveltyVehicles")
     * @Form\Options({"label":"application_vehicle-safety_undertakings-limousines"})
     */
    public $limousinesNoveltyVehicles;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
