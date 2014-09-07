<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("outcome")
 * @Form\Options({"label":"Outcome"})
 */
class Outcome
{
    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "presiding_tc"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $presidingTc = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "impound_outcome"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $outcome = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Outcome sent date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\DateNotRequiredNotInFuture")
     */
    public $outcomeSentDate = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Notes/ECMS number"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $notes = null;
}
