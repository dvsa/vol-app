<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Cases")
 * @Form\Attributes({"method":"post","label":"Case"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true,"label": "Case", "action_lcfirst": true})
 */
class Cases
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Cases")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
