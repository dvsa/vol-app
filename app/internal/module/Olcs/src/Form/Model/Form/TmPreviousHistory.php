<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-transport-manager-details")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class TmPreviousHistory
{
    /**
     * @Form\Name("previousHistory")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\PreviousHistory")
     */
    public $previousHistory = null;
}
