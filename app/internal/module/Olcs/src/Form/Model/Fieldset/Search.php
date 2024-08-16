<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("search")
 */
class Search
{
    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Licence number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $licNo = null;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Operator/trading name"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $operatorName = null;

    /**
     * @Form\Attributes({"class":"short","id":""})
     * @Form\Options({"label":"Postcode"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $postcode = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"long"})
     * @Form\Options({"label":"First name(s)"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"long"})
     * @Form\Options({"label":"Last name"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date of birth",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\DateNotRequiredNotInFuture")
     */
    public $birthDate = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "Search"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $search = null;
}
