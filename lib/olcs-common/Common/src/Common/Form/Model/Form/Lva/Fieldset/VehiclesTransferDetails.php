<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"label":""})
 */
class VehiclesTransferDetails
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"","placeholder":"","required":false})
     * @Form\Options({
     *     "label": "licence.vehicles_transfer.form.licence",
     *     "empty_option": "Please select"
     * })
     * @Form\Type("Select")
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *      options={
     *          "messages":{Laminas\Validator\NotEmpty::IS_EMPTY:"licence.vehicles_transfer.form.message_empty"}
     *      }
     * )
     */
    public $licence;
}
