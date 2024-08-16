<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("details")
 */
class TmResponsibilities
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
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.tm-type",
     *     "category": "tm_type",
     *     "fieldset-attributes": {
     *         "class": "checkbox inline"
     *     }
     * })
     * @Form\Type("DynamicRadio")
     * @Form\Required(false)
     * @Form\Validator({
     *      "name":"Laminas\Validator\NotEmpty"
     * })
     * @Form\Flags({"priority": -10})
     */
    public $tmType = null;

    /**
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.tm-app-status",
     *     "disable_inarray_validator": false,
     *     "category": "tmap_status"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"","placeholder":"", "required":false})
     * @Form\Type("DynamicSelect")
     * @Form\Flags({"priority": -20})
     */
    public $tmApplicationStatus = null;

    /**
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.is-owner",
     *     "value_options":{
     *         "Y":"Yes",
     *         "N":"No"
     *     },
     *     "fieldset-attributes": {
     *         "class": "checkbox inline"
     *     }
     * })
     * @Form\Type("Radio")
     * @Form\Flags({"priority": -30})
     */
    public $isOwner = null;

    /**
     * @Form\ComposedObject("Common\Form\Model\Fieldset\HoursOfWeek")
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.hours-per-week"
     * })
     * @Form\Flags({"priority": -40})
     */
    public $hoursOfWeek = null;

    /**
     * @Form\Name("otherLicences")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Flags({"priority": -50})
     */
    public $otherLicences = null;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"long"
     * })
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.additional-information",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     *
     * @Form\Required(false)
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator({
     *      "name":"Laminas\Validator\StringLength",
     *      "options":{
     *          "max":4000
     *      }
     * })
     * @Form\Flags({"priority": -60})
     */
    public $additionalInformation;

    /**
     * @Form\Attributes({"id":"file", "class": "file-upload"})
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Flags({"priority": -70})
     */
    public $file = null;

    /**
     * @Form\Options({
     *     "label": "tm-review-responsibility-training-undertaken",
     *     "value_options":{
     *         "Y":"Yes",
     *         "N":"No"
     *     },
     *     "fieldset-attributes": {
     *         "class": "checkbox inline"
     *     }
     * })
     * @Form\Type("Radio")
     * @Form\Flags({"priority": -80})
     */
    public $hasUndertakenTraining = null;
}
