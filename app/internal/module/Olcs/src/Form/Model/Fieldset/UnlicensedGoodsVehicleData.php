<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class UnlicensedGoodsVehicleData
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
     * @Form\Attributes({
     *     "class": "medium",
     *     "id": "vrm",
     *     "placeholder": "",
     * })
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.data.vrm",
     *     "error-message": "vehicle.error.top.vrm",
     * })
     * @Form\Type("\Common\Form\Elements\Custom\VehicleVrmAny")
     */
    public $vrm = null;

    /**
     * @Form\Attributes({
     *     "class": "small",
     *     "id": "plated_weight",
     *     "placeholder": "",
     * })
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.data.weight",
     *     "error-message": "vehicle.error.top.platedWeight",
     *     "allow_empty": true,
     * })
     * @Form\Type("\Common\Form\Elements\Custom\VehiclePlatedWeight")
     */
    public $platedWeight = null;
}
