<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

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
     * @Form\Options({"label": "internal-transport-manager-id"})
     * @Form\Name("transport-manager-id")
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     */
    public $transportManagerId = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "empty_option": "Please Select",
     *     "label": "transport-manager-details-title",
     *     "category":"person_title",
     *     "disable_inarray_validator": true,
     * })
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $title = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"transport-manager-details-first-name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator({"name": "\Laminas\Validator\NotEmpty"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $firstName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"transport-manager-details-last-name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator({"name": "\Laminas\Validator\NotEmpty"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $lastName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"transport-manager-details-email"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
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
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Laminas\Validator\NotEmpty"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $birthDate = null;

    /**
     * @Form\Attributes({"id":"","class":"medium"})
     * @Form\Options({"label":"transport-manager-details-place-of-birth"})
     * @Form\Validator({"name": "\Laminas\Validator\NotEmpty"})
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
