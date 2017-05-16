<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\AllowEmpty(true)
     * @Form\Required(false)
     * @Form\Attributes({"id":"email","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Email"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     */
    public $emailAddress = null;
}
