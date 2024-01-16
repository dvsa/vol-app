<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("user-contact")
 * @Form\Options({"label":"Contact"})
 */
class UserContact
{
    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"Email address"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     * @Form\Validator("Common\Form\Elements\Validators\EmailConfirm", options={"token":"emailConfirm"})
     */
    public $emailAddress = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"Confirm email address"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $emailConfirm = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Type("\Common\Form\Elements\InputFilters\Phone")
     */
    public $phone_primary = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $phone_primary_id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $phone_primary_version = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label": "secondary-contact-number-optional"})
     * @Form\Type("\Common\Form\Elements\InputFilters\Phone")
     */
    public $phone_secondary = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
    */
    public $phone_secondary_id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $phone_secondary_version = null;
}
