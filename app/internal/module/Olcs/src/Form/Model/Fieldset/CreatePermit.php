<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class CreatePermit extends Base
{
    /**
     * @Form\Options({
     *     "label": "<h4>ECMT Annual Permit Application</h4>",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     *
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $title = null;

    /**
     * @Form\Type("\Common\Form\Elements\Types\ReadonlyElement")
     * @Form\Options({
     *     "label": "Stock"
     * })
     */
    public $stockHtml = null;

    /**
     * @Form\Attributes({"id":"dateReceived"})
     * @Form\Options({
     *     "label": "Date received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $dateReceived = null;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     * @Form\Attributes({
     *    "id" : "numVehicles",
     * })
     *
     */
    public $numVehicles;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     * @Form\Type("\Common\Form\Elements\Types\ReadonlyElement")
     * @Form\Options({
     *     "label": "Current total vehicle authorization"
     * })
     *
     */
    public $numVehiclesLabel;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     * @Form\Attributes({
     *    "id" : "year",
     * })
     *
     */
    public $year = null;

    /**
     * @Form\Name("cabotage")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--cabotage",
     *   "id" : "cabotage",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "I confirm that I will not undertake any cabotage journeys using an ECMT permit.",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $cabotage = null;

    /**
     * @Form\Name("roadworthiness")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--roadworthiness",
     *   "id" : "cabotage",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "I understand that I must obtain and carry the appropriate ECMT Certificate of Compliance and Certificate of Roadworthiness for each vehicle and trailer I intend to use with this permit",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $roadworthiness = null;

    /**
     * @Form\Name("countrys")
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "The following countries have imposed limits on the number of permits for UK Hauliers. Please select any countries the applicant intends to travel to below.",
     *     "label_attributes":{
     *          "class" : "form-control form-control--checkbox"
     *     },
     *     "service_name": "Common\Service\Data\Country",
     *     "category": "ecmtConstraint",
     *     "disable_inarray_validator" : true,
     * })
     * @Form\Attributes({
     *     "class" : "chosen-select-large",
     *     "id" : "countrys",
     *     "allowWrap":true,
     *     "multiple":"multiple",
     *     "empty": "Select options if applicable",
     *     "data-container-class": "form-control__container",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $countrys = null;


    /**
     * @Form\Name("emissions")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--euro6",
     *    "id" : "emissions",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "I confirm that I will only use my ECMT permits with vehicles that are environmentally compliant with the minimum Euro emissions standards that the permit allows",
     *   "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *   "must_be_value": "Yes",
     *   "error-message": "error.messages.euro6",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $emissions = null;


    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--permits-requiredEuro5",
     *   "id" : "requiredEuro5",
     *   "step" : "any",
     *   "data-container-class": "js-hidden",
     * })
     * @Form\Options({
     *     "label": "Number of permits required for <strong>Euro5</strong> Emissions Standard",
     *     "hint": "",
     *     "short-label": "",
     *     "allow_empty" : true,
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     * @Form\Validator({
     *     "name": "SumCompare",
     *     "options": {
     *          "compare_to":"numVehicles",
     *          "operator":"lte",
     *          "sumWith" : "requiredEuro6",
     *          "allowEmpty" : true,
     *          "compare_to_label":"Your number of authorised vehicles",
     *     }
     * })
     * @Form\Type("Laminas\Form\Element\Number")
     */
    public $requiredEuro5 = 0;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--permits-requiredEuro6",
     *   "id" : "requiredEuro6",
     *   "step" : "any",
     *   "data-container-class": "js-hidden",
     * })
     * @Form\Options({
     *     "label": "Number of permits required for <strong>Euro6</strong> Emissions Standard",
     *     "hint": "",
     *     "short-label": "",
     *     "allow_empty" : true,
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     * @Form\Validator({
     *     "name": "SumCompare",
     *     "options": {
     *          "compare_to":"numVehicles",
     *          "operator":"lte",
     *          "sumWith" : "requiredEuro5",
     *          "allowEmpty" : true,
     *          "compare_to_label":"Your number of authorised vehicles",
     *     }
     * })
     * @Form\Type("Laminas\Form\Element\Number")
     */
    public $requiredEuro6 = 0;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *   "id" : "trips",
     *   "step" : "any",
     * })
     * @Form\Options({
     *     "label": "Number of trips abroad in the last 12 months",
     *     "short-label": "",
     * })
     * @Form\Type("Laminas\Form\Element\Number")
     */
    public $trips = null;

    /**
     * @Form\Name("internationalJourneys")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--international-journey",
     *    "id" : "internationalJourneys",
     * })
     * @Form\Options({
     *      "label": "International Journeys",
     *      "fieldset-attributes": {"id": "international-journey"},
     *      "fieldset-data-group": "percentage-type",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "category": "inter_journey_percentage",
     *      "error-message": "error.messages.international-journey"
     * })
     * @Form\Type("DynamicRadio")
     *
     *
     */
    public $internationalJourneys = null;

    /**
     * @Form\Name("sectors")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *    "id" : "EcmtSectorList",
     * })
     * @Form\Options({
     *      "label": "markup-ecmt-sector-list-label",
     *      "fieldset-attributes": {"id": "sector-list", "class":"inline"},
     *      "fieldset-data-group": "sector-list",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "disable_inarray_validator" : true,
     * })
     * @Form\Type("Radio")
     */
    public $sectors = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--declaration",
     *   "id" : "declaration",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "permits.form.declaration.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $declaration = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--checked",
     *   "id" : "checked",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "permits.form.checked.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $checked = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "id" : "status",
     *   "class": "permitApplicationStatus"
     * })
     *
     * @Form\Type("Laminas\Form\Element\Hidden")
     *
     */
    public $status;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "id" : "canBeCancelled",
     *   "class": "permitApplicationCanBeCancelled"
     * })
     *
     * @Form\Type("Laminas\Form\Element\Hidden")
     *
     */
    public $canBeCancelled;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "id" : "irhpPermitStock",
     * })
     *
     * @Form\Type("Laminas\Form\Element\Hidden")
     *
     */
    public $irhpPermitStock;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     * @Form\Attributes({
     *    "id" : "licenceId",
     * })
     *
     */
    public $licence;
}
