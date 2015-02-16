<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("transport-manager-details")
 */
class TransportManagerDetails
{
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
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "transport-manager-details-title",
     *     "value_options": {
     *         "Mr": "transport-manager-details-title-value-mr",
     *         "Mrs": "transport-manager-details-title-value-mrs",
     *         "Miss": "transport-manager-details-title-value-miss",
     *         "Ms": "transport-manager-details-title-value-ms"
     *     },
     *     "empty_option": "transport-manager-details-please-select",
     *     "disable_inarray_validator": false,
     * })
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $title = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"transport-manager-details-first-name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":35}})
     */
    public $firstName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"transport-manager-details-last-name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":35}})
     */
    public $lastName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"transport-manager-details-email"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\EmailAddress"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":255}})
     */
    public $emailAddress = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob","required":false})
     * @Form\Options({
     *     "label": "transport-manager-details-dob",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Validator({"name":"\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $birthDate = null;

    /**
     * @Form\Attributes({"id":"","class":"medium"})
     * @Form\Options({"label":"transport-manager-details-place-of-birth"})
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Type("Text")
     */
    public $birthPlace = null;

    /**
     * @Form\Options({
     *     "label": "transport-manager-details-type",
     *     "category": "tm_type",
     * })
     * @Form\Type("DynamicRadio")
     */
    public $type = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $homeCdId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $homeCdVersion = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $workCdId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $workCdVersion = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $personId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $personVersion = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $status = null;
}
