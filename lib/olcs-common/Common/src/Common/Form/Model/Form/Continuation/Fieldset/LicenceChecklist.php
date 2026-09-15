<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class LicenceChecklist
{
    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"typeOfLicenceCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#typeOfLicenceCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *      "label":"continuations.type-of-licence-checkbox.label",
     *      "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *      "content":"partials/continuation/licence-checklist-type-of-licence",
     *      "checked_value":"Y",
     *      "unchecked_value":"N",
     *      "must_be_value": "Y",
     *      "not_checked_message":"continuations.checklist.section.error.type-of-licence",
     * })
     */
    public $typeOfLicenceCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"businessTypeCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#businessTypeCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.business-type-checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-business-type",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.business-type"
     * })
     */
    public $businessTypeCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"businessDetailsCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#businessDetailsCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.business-details-checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-business-details",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.business-details"
     * })
     */
    public $businessDetailsCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"addressesCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#addressesCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.addresses-checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-addresses",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.addresses"
     * })
     */
    public $addressesCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"peopleCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#peopleCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.people-checkbox.label.",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-people",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.people."
     * })
     */
    public $peopleCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"operatingCentresCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#operatingCentresCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.operatingCentres-checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-operating-centres",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.operatingCentres"
     * })
     */
    public $operatingCentresCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"transportManagersCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#transportManagersCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.transportManagers-checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-transport-managers",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.transportManagers"
     * })
     */
    public $transportManagersCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"vehiclesCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#vehiclesCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.vehicles-checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-vehicles",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.vehicles"
     * })
     */
    public $vehiclesCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"safetyCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#safetyCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.safety-checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-safety",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.safety"
     * })
     */
    public $safetyCheckbox;

    /**
     * @Form\Type("Common\Form\Elements\Types\CheckboxAdvanced")
     * @Form\Required(true)
     * @Form\Attributes({
     *      "id":"usersCheckbox",
     *      "data-js-validate":"required",
     *      "data-show-element":"#usersCheckbox-hidden",
     *      "class":"checkbox"
     * })
     * @Form\Options({
     *     "label":"continuations.users-checkbox.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "content":"partials/continuation/licence-checklist-users",
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y",
     *     "not_checked_message":"continuations.checklist.section.error.users"
     * })
     */
    public $usersCheckbox;
}
