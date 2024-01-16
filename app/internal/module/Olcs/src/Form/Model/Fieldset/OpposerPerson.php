<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("opposerPerson")
 */
class OpposerPerson
{
    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Options({"label":"Contact first name"})
     * @Form\Attributes({"id":"forename","placeholder":"","class":"medium", "required":false})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":35})
     */
    public $forename = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Options({"label":"Contact family name"})
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":35})
     */
    public $familyName = null;
}
