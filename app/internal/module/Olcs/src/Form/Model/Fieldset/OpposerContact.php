<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("opposerContact")
 */
class OpposerContact
{
    /**
     * @Form\Attributes({"id":"phone","placeholder":"","class":"medium"})
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
     * @Form\Attributes({"id":"phone","placeholder":"","class":"medium"})
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

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"id":"email","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"Email"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     */
    public $emailAddress = null;
}
