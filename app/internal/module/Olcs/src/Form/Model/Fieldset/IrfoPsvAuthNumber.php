<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("irfo-psv-auth-number")
 */
class IrfoPsvAuthNumber
{
    /**
     * @Form\Attributes({"class":"","id":"irfoPsvAuthNumber"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator({"name":"Laminas\Validator\StringLength","options":{"min":1,"max":70}})
     */
    public $name = null;
}
