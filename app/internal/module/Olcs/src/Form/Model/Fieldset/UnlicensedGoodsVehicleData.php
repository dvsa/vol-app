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
     * @Form\Attributes({"class":"medium","id":"vrm","placeholder":""})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.data.vrm"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Common\Filter\Vrm"})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\Vrm"})
     */
    public $vrm = null;

    /**
     * @Form\Attributes({"class":"small","id":"plated_weight","placeholder":""})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.data.weight"
     * })
     * @Form\Validator({"name": "Zend\Validator\Digits"})
     * @Form\Validator({"name": "Zend\Validator\Between", "options": {"min": 0, "max": 999999}})
     * @Form\Type("Text")
     * @Form\Required(false)
     */
    public $platedWeight = null;
}
