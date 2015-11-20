<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 * @Form\Name("user-contact")
 * @Form\Options({"label":"Contact"})
 */
class UserContactDetails
{
    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"Email address"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\EmailConfirm","options":{"token":"emailConfirm"}})
     */
    public $emailAddress = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"Confirm email address"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $emailConfirm = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(false)
     * @Form\Attributes({"id":"phone","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Phone"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":45}})
     * @Form\Name("phone_business")
     */
    public $phoneBusiness = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Name("phone_business_id")
     */
    public $phoneBusinessId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Name("phone_business_version")
     */
    public $phoneBusinessVersion = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(false)
     * @Form\Attributes({"id":"fax","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Fax"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":45}})
     * @Form\Name("phone_fax")
     */
    public $phoneFax = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Name("phone_fax_id")
     */
    public $phoneFaxId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Name("phone_fax_version")
     */
    public $phoneFaxVersion = null;
}
