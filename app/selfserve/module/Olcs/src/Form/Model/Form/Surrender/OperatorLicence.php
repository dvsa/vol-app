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

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButton")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;

    /**
     * @Form\Attributes({"type":"submit"})
     * @Form\Options({"label":"licence.surrender.operator_licence.return_to_current_discs.link"})
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $currentDiscsLink = null;
}
