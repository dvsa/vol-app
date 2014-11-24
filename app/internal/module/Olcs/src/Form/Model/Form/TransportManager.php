<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("transport-manager")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class TransportManager
{
    /**
     * @Form\Name("transport-manager-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TransportManagerDetails")
     */
    public $transportManagerDetails = null;

    /**
     * @Form\Name("address")
     * @Form\Options({"label":"Address"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     */
    public $address = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TransportManagerActions")
     */
    public $transportManagerActions = null;
}
