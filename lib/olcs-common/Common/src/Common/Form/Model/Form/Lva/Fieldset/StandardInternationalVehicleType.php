<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

class StandardInternationalVehicleType
{
    /**
     * @Form\Name("vehicle-type")
     * @Form\Options({
     *      "error-message": "type-of-vehicle-error",
     *      "label": "application_type-of-licence_licence-type.data.vehicleType",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "hint": "application_type-of-licence_licence-type.data.vehicleType.hint",
     *      "value_options": {
     *          \Common\RefData::APP_VEHICLE_TYPE_LGV: {
     *             "value":\Common\RefData::APP_VEHICLE_TYPE_LGV,
     *             "label":"select-option-yes",
     *          },
     *          \Common\RefData::APP_VEHICLE_TYPE_MIXED: {
     *             "value":\Common\RefData::APP_VEHICLE_TYPE_MIXED,
     *             "label":"select-option-no",
     *          }
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $vehicleType;

    /**
     * @Form\Name("lgv-declaration")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\LgvDeclaration")
     * @Form\Options({
     *     "label": "application_type-of-licence_licence-type.data.lgvDeclaration",
     * })
     */
    public $lgvDeclaration;
}
