<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("User")
 * @Form\Attributes({"method":"post","label":"User"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "User"})
 */
class User
{
    /**
     * @Form\Name("main")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\UserDetails")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $contactDetailsId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $contactDetailsVersion = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $personId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $personVersion = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $contactType = null;
}
