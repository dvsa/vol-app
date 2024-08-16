<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter": true})
 * @Form\Name("unlicensed-goods-vehicles-vehicle")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class AddUnlicensedGoodsVehicle
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\UnlicensedGoodsVehicleData")
     */
    public $data = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions = null;
}
