<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("type-of-licence")
 * @Form\Options({"label":"Type of licence"})
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class TypeOfLicence
{
    /**
     * @Form\Name("operator-location")
     * @Form\Options({"label":"Operator location"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorLocation")
     */
    // public $operatorLocation = null;

    /**
     * @Form\Name("operator-type")
     * @Form\Options({"label":"Operator type"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorType")
     */
    // public $operatorType = null;

    /**
     * @Form\Name("licence-type")
     * @Form\Options({"label":"Licence type"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceType")
     */
    // public $licenceType = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Form\Fieldset\FormActions")
     */
    public $formActions = null;
}
