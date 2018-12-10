<?php

namespace Olcs\Form\Model\Form\Surrender;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("operator-licence")
 */
class OperatorLicence
{
    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\Fieldset\OperatorLicenceDocument")
     */
    public $operatorLicenceDocument = null;
}