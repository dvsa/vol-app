<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("EnvironmentalComplaint")
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 * @Form\Attributes({"method":"post"})
 */
class EnvironmentalComplaint
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":"","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\EnvironmentalComplaint")
     */
    public $fields = null;

    /**
     * @Form\Name("address")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\RequestorsAddress")
     */
    public $address = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
