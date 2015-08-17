<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter": true, "label":"Edit vehicle"})
 * @Form\Name("unlicensed-psv-vehicles-vehicle")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class EditUnlicensedPsvVehicle
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\UnlicensedPsvVehicleData")
     */
    public $data = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
