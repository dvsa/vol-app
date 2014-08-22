<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class Fields
{

    /**
     * @Form\Attributes({"id":"dob","class":"extra-long"})
     * @Form\Options({
     *     "label": "Date of request",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\DateRequired")
     */
    public $requestDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "case_stay_outcome"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $outcome = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Notes",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-6",
     *     "help-block": "You can type anything in this box."
     * })
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $notes = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Is
     * withdrawn?"})
     * @Form\Type("checkbox")
     */
    public $isWithdrawn = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Withdrawn date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\WithdrawnDate")
     */
    public $withdrawnDate = null;


}

