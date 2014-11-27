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
     * @Form\Name("home-address")
     * @Form\Options({"label":"transport-manager-details-home-address"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\RegisteredAddress")
     */
    public $homeAddress = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TransportManagerActions")
     */
    public $transportManagerActions = null;
}
