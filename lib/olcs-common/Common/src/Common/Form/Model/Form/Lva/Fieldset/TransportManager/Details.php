<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lva-transport-manager-details")
 * @Form\Attributes({"id":"details"})
 */
class Details
{
    /**
     * @Form\Options({"label":"lva-tm-details-details-name"})
     * @Form\Type("\Common\Form\Elements\Types\ReadonlyElement")
     * @Form\Flags({"priority": -10})
     */
    public $name;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "dob",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldset_attributes":{"id":"details[birthDate]"}
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     * @Form\Flags({"priority": -20})
     */
    public $birthDate;

    /**
     * @Form\Attributes({"class":"extra-long","id":"emailAddress"})
     * @Form\Options({
     *     "label":"lva-tm-details-details-email",
     *     "short-label": "lva-tm-details-details-email",
     *     "hint": "lva-tm-email-hint",
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     * @Form\Flags({"priority": -30})
     */
    public $emailAddress;

    /**
     * @Form\Attributes({"id":"birthPlace","class":"medium"})
     * @Form\Options({
     *     "label": "lva-tm-details-details-birthPlace",
     *     "short-label": "lva-tm-details-details-birthPlace",
     *     "label_attributes": {
     *         "aria-label": "Enter their place of birth"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Validator("\Laminas\Validator\NotEmpty")
     * @Form\Flags({"priority": -40})
     */
    public $birthPlace;

    /**
     * @Form\Attributes({"value": "markup-lva-tm-details-details-certificateHtml"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Flags({"priority": -50})
     */
    public $certificateHtml;

    /**
     * @Form\Attributes({"id":"certificate", "class": "file-upload"})
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Options({
     *    "label":"lva-tm-details-details-certificate",
     *    "hint": "markup-professional-competence-certificates-obtained-abroad.phtml",
     *    "hint-position": "above",
     *    "label_attributes": {
     *        "class": "legend",
     *        "aria-label": "Certificate of professional competence, attach file(s) for upload",
     *    },
     * })
     * @Form\Flags({"priority": -60})
     */
    public $certificate;

    /**
     * @Form\Attributes({"value": "markup-lva-tm-details-details-lgvAcquiredRightsHtml"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Flags({"priority": -70})
     */
    public $lgvAcquiredRightsHtml;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"id":"lgv-acquired-rights-ref-number","class":"medium"})
     * @Form\Options({
     *     "label": "lva-tm-details-details-lgvAcquiredRightsReferenceNumber",
     *     "label_attributes": {"class": "legend"},
     *     "hint": "lva-tm-details-details-lgvAcquiredRightsReferenceNumber-hint",
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("\Laminas\Validator\StringLength",
     *     options={
     *         "min": 7,
     *         "max": 7,
     *         "messages": {
     *             \Laminas\Validator\StringLength::INVALID: "lva-tm-details-details-lgvAcquiredRightsReferenceNumber-error-length",
     *             \Laminas\Validator\StringLength::TOO_SHORT: "lva-tm-details-details-lgvAcquiredRightsReferenceNumber-error-length",
     *             \Laminas\Validator\StringLength::TOO_LONG: "lva-tm-details-details-lgvAcquiredRightsReferenceNumber-error-length",
     *         }
     *     }
     * )
     * @Form\Flags({"priority": -80})
     */
    public $lgvAcquiredRightsReferenceNumber;

    /**
     * @Form\Options({
     *     "label": "tm-review-responsibility-training-undertaken",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "hint" : "tm-hint-responsibility-training-undertaken",
     *     "hint-position" : "below",
     *     "hint-class" : "govuk-radios__conditional govuk-body hint hint__below hint__black hintNoTraining",
     * })
     * @Form\Type("Radio")
     * @Form\Flags({"priority": -90})
     */
    public $hasUndertakenTraining;
}
