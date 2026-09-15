<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"table__form","id":"responsibilities"})
 * @Form\Name("responsibilities")
 */
class Responsibilities
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.tm-type",
     *     "category": "tm_type",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Type("DynamicRadio")
     * @Form\Validator("Laminas\Validator\NotEmpty")
     * @Form\Flags({"priority": -10})
     */
    public $tmType;

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
    public $tmApplicationStatus;

    /**
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.is-owner",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "hint" : "transport-manager.responsibilities.is-owner.no.hint",
     *     "hint-position" : "below",
     *     "hint-class" : "govuk-radios__conditional govuk-body hint hint__below hint__black hintNoOwner",
     * })
     * @Form\Type("Radio")
     * @Form\Flags({"priority": -30})
     */
    public $isOwner;

    /**
     * @Form\Name("hoursOfWeek")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\HoursOfWeekRequired")
     * @Form\Attributes({"id":"hoursOfWeek"})
     * @Form\Flags({"priority": -40})
     */
    public $hoursOfWeek;

    /**
     * @Form\Name("otherLicencesFieldset")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\OtherLicencesFieldset")
     * @Form\Flags({"priority": -50})
     */
    public $otherLicencesFieldset;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"long",
     *      "label": "transport-manager.responsibilities.additional-information.title",
     *      "autocomplete": "nope",
     * })
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.additional-information",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     },
     *     "label_attributes": {"id":"additionalInformation"}
     * })
     * @Form\Required(false)
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength",
     *      options={
     *          "max":4000
     *      }
     * )
     * @Form\Flags({"priority": -60})
     */
    public $additionalInformation;

    /**
     * @Form\Attributes({"id":"file", "class": "file-upload"})
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Flags({"priority": -70})
     */
    public $file;
}
