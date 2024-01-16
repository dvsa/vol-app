<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class Publication extends Base
{
    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({"label":"Text 1"})
     * @Form\Type("Textarea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":4000})
     */
    public $text1 = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({"label":"Text 2"})
     * @Form\Type("Textarea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":4000})
     */
    public $text2 = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({"label":"Text 3"})
     * @Form\Type("Textarea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":4000})
     */
    public $text3 = null;
}
