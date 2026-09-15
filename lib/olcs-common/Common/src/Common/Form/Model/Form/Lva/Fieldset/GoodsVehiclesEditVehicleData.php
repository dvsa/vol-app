<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class GoodsVehiclesEditVehicleData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({
     *     "class":"medium",
     *     "id":"vrm",
     *     "placeholder":"",
     *     "disabled":"disabled"
     * })
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.data.vrm",
     * })
     * @Form\Type("Text")
     */
    public $vrm;

    /**
     * @Form\Attributes({
     *     "class":"small",
     *     "id":"plated_weight",
     *     "placeholder":"",
     *     "pattern":"\d"
     * })
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.data.weight",
     *     "error-message": "vehicle.error.top.platedWeight",
     * })
     * @Form\Type("\Common\Form\Elements\Custom\VehiclePlatedWeight")
     */
    public $platedWeight;
}
