<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("transport-manager")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "Transport Manager", "action_lcfirst": false})
 */
class TransportManager
{
    /**
     * @Form\Name("transport-manager-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TransportManagerDetails")
     */
    public $transportManagerDetails = null;

    /**
     * @Form\Name("home-address")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\AddressOptional")
     * @Form\Options({"label":"transport-manager-details-home-address"})
     */
    public $homeAddress = null;

    /**
     * @Form\Name("work-address")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\AddressOptional")
     * @Form\Options({"label":"transport-manager-details-work-address"})
     */
    public $workAddress = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TransportManagerActions")
     */
    public $transportManagerActions = null;
}
