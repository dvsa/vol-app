<?php

namespace Olcs\Form\Model\Form\Lva;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("User")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class NewTmUser
{
    /**
     * @Form\Name("data")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Form\Lva\Fieldset\NewTmUserDetails")
     */
    public $data = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\ContinueFormActions")
     */
    public $formActions = null;
}
