<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("details")
 */
class TransportManagerApplicationOrLicenceFullDetails
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
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium",  "multiple" : true})
     * @Form\Options({
     *     "label": "internal.transport-manager.responsibilities.tm-application-oc",
     *     "service_name": "Olcs\Service\Data\ApplicationOperatingCentre",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $tmApplicationOc = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium",  "multiple" : true})
     * @Form\Options({
     *     "label": "internal.transport-manager.responsibilities.tm-application-oc",
     *     "service_name": "Common\Service\Data\LicenceOperatingCentre",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $tmLicenceOc = null;

    /**
     * @Form\Options({
     *     "label": "internal.transport-manager.responsibilities.tm-type",
     *     "category": "tm_type",
     * })
     * @Form\Type("DynamicRadio")
     * @Form\Validator({
     *      "name":"Zend\Validator\NotEmpty"
     * })
     */
    public $tmType = null;

    /**
     * @Form\Options({
     *     "label": "internal.transport-manager.responsibilities.hours-per-week",
     *     "subtitle": "internal.transport-manager.responsibilities.hours-per-week-subtitle"
     * })
     * @Form\Type("Common\Form\Elements\Types\HoursPerWeek")
     */
    public $hoursOfWeek = null;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"long"
     * })
     * @Form\Options({
     *     "label": "internal.transport-manager.responsibilities.additional-information",
     *     "help-block": "Please provide additional information relating to any prior insolvency proceedings.
     You may also upload evidence such as a legal documents.",
     *     "label_attributes": {
     *         "class": "long"
     *     },
     *     "column-size": "",
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":4000
     *      }
     * })
     */
    public $additionalInformation;

    /**
     * @Form\Attributes({"id":"file", "class": "file-upload"})
     * @Form\Type("\Common\Form\Elements\Types\MultipleFileUpload")
     */
    public $file = null;
}
