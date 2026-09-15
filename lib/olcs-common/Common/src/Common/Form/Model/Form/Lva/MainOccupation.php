<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-main-occupation")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class MainOccupation
{
    /**
     * @Form\Name("version")
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Name("mainOccupation")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\VehiclesDeclarationsMainOccupation")
     * @Form\Options({"label":"application_vehicle-safety_undertakings.mainOccupation"})
     */
    public $mainOccupation;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
