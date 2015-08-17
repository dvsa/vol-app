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
class UnlicensedOperator
{
    /**
     * @Form\Name("operator-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\UnlicensedOperatorDetails")
     */
    public $operatorDetails = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\UnlicensedOperatorAddress")
     * @Form\Options({"label": "Correspondence address"})
     */
    public $correspondenceAddress = null;

    /**
     * @Form\ComposedObject("Common\Form\Model\Fieldset\ContactOptional")
     */
    public $contact = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorActions")
     */
    public $formActions = null;
}
