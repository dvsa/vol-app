<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("UserRegistration")
 * @Form\Attributes({"method":"post","label":"user-registration.form.label"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "user-registration.form.label"})
 */
class UserRegistration
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\UserRegistration")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CreateAccountButtons")
     */
    public $formActions = null;
}
