<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("User")
 * @Form\Attributes({"method":"post","label":"User"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "User"})
 */
class User extends Base
{
    /**
     * @Form\Name("userType")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\UserType")
     */
    public $userType = null;

    /**
     * @Form\Name("userPersonal")
     * @Form\Options({"label":"Personal"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\UserPersonal")
     */
    public $userPersonal = null;

    /**
     * @Form\Name("userContactDetails")
     * @Form\Options({"label":"Personal"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\UserContactDetails")
     */
    public $userContactDetails = null;

    /**
     * @Form\Name("address")
     * @Form\Options({"label":"Address"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\AddressOptional")
     */
    public $address = null;

    /**
     * @Form\Name("userLoginSecurity")
     * @Form\Options({"label":"Security"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\UserLoginSecurity")
     */
    public $userLoginSecurity = null;

    /**
     * @Form\Attributes({"value":"ct_obj"})
     * @Form\Type("Hidden")
     */
    public $contactDetailsType = null;

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
    public $phoneContactId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $phoneContactVersion = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
