<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("details")
 * @Form\Options({"label":"Statement Details"})
 */
class StatementDetails
{

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Statement type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "statement_types"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $statementType = null;

    /**
     * @Form\Options({"label":"Vehicle registration mark"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Filter({"name":"Zend\Filter\StringToUpper"})
     * @Form\Filter({
     *     "name": "Zend\Filter\PregReplace",
     *     "options": {
     *         "pattern": "/\ /",
     *         "replacement": ""
     *     }
     * })
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":7}})
     * @Form\Validator({"name":"Zend\I18n\Validator\Alnum"})
     */
    public $vrm = null;

    /**
     * @Form\Attributes({"placeholder":""})
     * @Form\Options({"label":"Requestors first name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":35}})
     */
    public $requestorsForename = null;

    /**
     * @Form\Attributes({"placeholder":""})
     * @Form\Options({"label":"Requestors last name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":35}})
     */
    public $requestorsFamilyName = null;

    /**
     * @Form\Attributes({"class":"","id":""})
     * @Form\Options({"label":"Requestor body"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":40}})
     */
    public $requestorsBody = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date stopped",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\StopDateBeforeRequestDate")
     */
    public $stoppedDate = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date requested",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\DateNotInFuture")
     */
    public $requestedDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Request mode",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "contact_method"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $contactType = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Authorised decision",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-6",
     *     "help-block": "You can type anything in this box."
     * })
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $authorisersDecision = null;


}

