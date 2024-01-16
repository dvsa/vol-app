<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("contact-details")
 */
class ContactDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"contact-details-first-name"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("\Laminas\Validator\NotEmpty")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $firstName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"contact-details-last-name"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator({"name": "\Laminas\Validator\NotEmpty"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $lastName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"contact-details-email"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     */
    public $emailAddress = null;

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
    public $status = null;
}
