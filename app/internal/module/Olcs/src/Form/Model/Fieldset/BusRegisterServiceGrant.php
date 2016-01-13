<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-service-grant")
 */
class BusRegisterServiceGrant
{
    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Options({
     *     "label": "Granting a bus registration",
     * })
     * @Form\Attributes({
     *      "id":"grantValidation",
     *      "value":"All questions must be answered 'Yes' before the registration can be granted"
     * })
     */
    public $grantValidation = null;
}
