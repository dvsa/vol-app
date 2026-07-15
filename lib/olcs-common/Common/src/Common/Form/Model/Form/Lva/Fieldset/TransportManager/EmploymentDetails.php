<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-employment-details")
 * @Form\Options({"label": "transport-manager.employment.form.position.title"})
 */
class EmploymentDetails
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
     * @Form\Attributes({"class":"long"})
     * @Form\Options({
     *     "label":"transport-manager.employment.form.position",
     *     "short-label":"transport-manager.employment.form.position"
     * })
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\StringLength",
     *     options={
     *          "max":45,
     *     },
     * )
     */
    public $position;

    /**
     * @Form\Attributes({
     *     "class":"long",
     *     "placeholder": "transport-manager.employment.form.hoursPerWeek.placeholder",
     * })
     * @Form\Options({
     *     "label":"transport-manager.employment.form.hoursPerWeek",
     *     "short-label":"transport-manager.employment.form.hoursPerWeek",
     *     "error-message": "transport-manager.employment.form.hoursPerWeek.errorMessage",
     * })
     * @Form\Type("Textarea")
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\StringLength",
     *     options={
     *          "max":300,
     *     },
     * )
     */
    public $hoursPerWeek;

    /**
     * @Form\Attributes({
     *     "value": "<div>%s<br></div>",
     *     "data-container-class": "help__text"
     * })
     * @Form\Options({"tokens":{"transportManager.data.availability.availabilityGuidance"}})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $availabilityGuidance;

    /**
     * @Form\Name("understood-availability-agreement-confirmation")
     * @Form\Attributes({"id": "understoodAvailabilityAgreement", "placeholder": ""})
     * @Form\Options({
     *     "label": "transportManager.data.availability.understoodAvailabilityAgreementConfirmation",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "label_attributes": {
     *         "class": "form-control form-control--checkbox form-control--advanced",
     *     }
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $understoodAvailabilityAgreementConfirmation;
}
