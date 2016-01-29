<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("operator")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Operator
{
    /**
     * @Form\Name("operator-id")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorId")
     */
    public $operatorId = null;

    /**
     * @Form\Name("operator-business-type")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorBusinessType")
     */
    public $operatorBusinessType = null;

    /**
     * @Form\Name("operator-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorDetails")
     */
    public $operatorDetails = null;

    /**
     * @Form\Options({"label": "Registered address"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\OperatorRegisteredAddress")
     */
    public $registeredAddress = null;

    /**
     * @Form\Name("operator-cpid")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorCpid")
     */
    public $operatorCpid = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorActions")
     */
    public $formActions = null;
}
