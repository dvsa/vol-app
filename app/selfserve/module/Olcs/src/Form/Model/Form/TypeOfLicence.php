<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("type-of-licence")
 * @Form\Options({"label":"Type of cicence"})
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class TypeOfLicence
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":"Appeal Details"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Appeal")
     */
    //public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Form\Fieldset\FormActions")
     */
    public $formActions = null;
}
