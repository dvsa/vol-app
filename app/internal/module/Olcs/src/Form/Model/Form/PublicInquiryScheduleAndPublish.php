<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Public inquiry Schedule and Publish")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class PublicInquiryScheduleAndPublish
{
    /**
     * @Form\Attributes({"class":"","id":""})
     * @Form\Options({"label":"PI number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $piNumber = null;

    /**
     * @Form\Attributes({"id":"dob","class":"long"})
     * @Form\Options({
     *     "label": "Agreed date",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $agreedDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Legislation",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $legislation = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Venue",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $venue = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Other venue"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $otherVenue = null;

    /**
     * @Form\Attributes({"id":"dob","class":"long"})
     * @Form\Options({
     *     "label": "Date of PI",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $piDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/TR/DTR",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $presidingTc = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/TR/DTR role",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $presidingRole = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({
     *     "label": "Witnesses",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $witnesses = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Cancelled",
     *     "value_options": {
     *
     *     },
     *     "help-block": "Please choose",
     *     "must_be_checked": true
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\Checkbox")
     */
    public $cancelled = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Ajourned",
     *     "value_options": {
     *
     *     },
     *     "help-block": "Please choose",
     *     "must_be_checked": true
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\Checkbox")
     */
    public $ajourned = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Definitions for consideration",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $definitionsForConsideration = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Details to be published",
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
    public $detailsToBePublished = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $case = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({
     *     "label": "Save",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $submit = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large"})
     * @Form\Options({
     *     "label": "Cancel",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $cancel = null;
}
