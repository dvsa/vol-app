<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("Laminas\Form\Fieldset")
 * @Form\Name("user-login")
 * @Form\Options({"label":"Personal"})
 */
class UserPersonal
{
    /**
     * @Form\Attributes({"id":"forename","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"First name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":35})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"Last name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator({"name":"Laminas\Validator\StringLength","options":{"max":35}})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Birth date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Required(false)
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $birthDate = null;
}
