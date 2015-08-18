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
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-5",
     *     "help-block": "Between 2 and 50 characters."
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Common\Filter\Vrm"})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\Vrm"})
     */
    public $vrm = null;

    /**
     * @Form\Type("Select")
     * @Form\Options({
     *      "label": "Type",
     *      "empty_option": "",
     *      "value_options":{
     *          "vhl_t_a":"internal-operator-unlicensed-vehicles.type.vhl_t_a",
     *          "vhl_t_b":"internal-operator-unlicensed-vehicles.type.vhl_t_b",
     *          "vhl_t_c":"internal-operator-unlicensed-vehicles.type.vhl_t_c"
     *      }
     * })
     * @Form\Required(false)
     */
    public $psvType = null;
}
