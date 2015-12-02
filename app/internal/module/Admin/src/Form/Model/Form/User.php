<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("User")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
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
     * @Form\Name("userSettings")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\UserSettings")
     */
    public $userSettings = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
