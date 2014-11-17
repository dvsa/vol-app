<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("opertor")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Operator
{
    /**
     * @Form\Name("operator-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorDetails")
     */
    public $operatorDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorActions")
     */
    public $formActions = null;
}
