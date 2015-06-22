<?php

namespace Olcs\Form\Model\Form\Lva;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-goods-vehicles-vehicle")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class EditGoodsVehicle
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\GoodsVehiclesEditVehicleData")
     */
    public $data = null;

    /**
     * @Form\Name("licence-vehicle")
     * @Form\Type("Fieldset")
     */
    public $licenceVehicle = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormEditCrudActions")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
