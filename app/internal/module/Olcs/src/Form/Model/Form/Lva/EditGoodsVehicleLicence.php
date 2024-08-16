<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-goods-vehicles-vehicle")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class EditGoodsVehicleLicence
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\GoodsVehiclesEditVehicleData")
     */
    public $data = null;

    /**
     * @Form\Name("licence-vehicle")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\LicenceLicenceVehicle")
     */
    public $licenceVehicle = null;

    /**
     * @Form\Name("vehicle-history-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $vehicleHistoryTable;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormEditCrudActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions = null;
}
