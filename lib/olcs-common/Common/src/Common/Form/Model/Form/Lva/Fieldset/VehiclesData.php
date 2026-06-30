<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Vehicle Data
 */
class VehiclesData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"js-enabled"})
     * @Form\Options({
     *     "error-message": "vehiclesDate_hasEnteredReg-error",
     *     "label": "application_vehicle-safety_vehicle-psv.hasEnteredReg",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "value_options": {"Y":"Yes", "N":"No"}
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $hasEnteredReg;

    /**
     * @Form\Attributes({"value":"markup-application_vehicle-notice"})
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice;
}
