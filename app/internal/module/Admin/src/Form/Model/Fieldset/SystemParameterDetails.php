<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("system-parameter-details")
 */
class SystemParameterDetails
{
    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Key"})
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":32})
     */
    public $id = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Param value"})
     * @Form\Type("TextArea")
     * @Form\Required(true)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":1024})
     */
    public $paramValue = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Description"})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $description = null;

    /**
     * @Form\Name("hiddenId")
     * @Form\Type("Hidden")
     */
    public $hiddenId = null;
}
