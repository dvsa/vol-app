<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("data")
 * @Form\Options({})
 */
class UnlicensedPsvVehicleData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Required(false)
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Required(false)
     */
    public $version = null;

    /**
     * @Form\Attributes({"id":"vrm","placeholder":""})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-psv-sub-action.data.vrm",
     *     "error-message": "vehicle.error.top.vrm",
     * })
     * @Form\Type("\Common\Form\Elements\Custom\VehicleVrmAny")
     */
    public $vrm = null;
}
