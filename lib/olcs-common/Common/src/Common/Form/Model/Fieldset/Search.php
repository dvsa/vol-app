<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("search")
 */
class Search
{
    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Lic #"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $licNo;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Operator / trading name"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $operatorName;

    /**
     * @Form\Attributes({"class":"short","id":""})
     * @Form\Options({"label":"Postcode"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $postcode;

    /**
     * @Form\Attributes({"placeholder":"","class":"long"})
     * @Form\Options({"label":"First name(s)"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $forename;

    /**
     * @Form\Attributes({"placeholder":"","class":"long"})
     * @Form\Options({"label":"Last name"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $familyName;
}
