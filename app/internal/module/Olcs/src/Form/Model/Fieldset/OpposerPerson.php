<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $forename = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Options({"label":"Contact family name"})
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $familyName = null;
}
