<?php

namespace Olcs\Form\Model\Form\Surrender;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("operator-licence")
 * @Form\Type("\Common\Form\Form")
 */
class OperatorLicence
{
    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\Fieldset\OperatorLicenceDocument")
     */
    public $operatorLicenceDocument = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButton")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions = null;
}
