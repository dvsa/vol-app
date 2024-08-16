<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("details")
 */
class TransportManagerApplicationSmallDetails
{
    /**
     * @Form\Options({"label":"transport-manager.responsibilities.application-id"})
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"","required":false})
     * @Form\Type("Text")
     */
    public $application = null;
}
