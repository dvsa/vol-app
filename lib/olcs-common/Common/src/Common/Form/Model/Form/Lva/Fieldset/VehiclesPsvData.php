<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Vehicles Psv Data
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesPsvData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"js-enabled"})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-psv.hasEnteredReg",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "value_options": {"Y":"Yes", "N":"No"}
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $hasEnteredReg;
}
