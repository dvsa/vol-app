<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class CreatePermit extends Base
{


    /**
     * @Form\Options({
     *     "label": "<h4>Permit Application</h4>",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     *
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $title = null;


    /**
     * @Form\Attributes({"id":"permitType"})
     * @Form\Options({
     *     "label": "Permit Type",
     *     "short-label": "Permit Type",
     *     "label_attributes": {"id": "label-permit-type"},
     * "value_options": {
     *          "ECMT",
     *      },
     * })
     * @Form\Type("Select")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $permitType = null;

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
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $dateReceived = null;


    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--permits-required",
     *   "id" : "permitsRequired",
     *   "step" : "any"
     * })
     * @Form\Options({
     *     "label": "Number of permits required (Cannot exceed total authorized vehicles)",
     *     "hint": "",
     *     "short-label": "",
     *     "allow_empty" : true,
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Validator({"name":"Zend\Validator\GreaterThan", "options": {"min": 0}})
     * @Form\Validator({
     *     "name": "NumberCompare",
     *     "options": {
     *          "compare_to":"numVehicles",
     *          "operator":"lte",
     *          "compare_to_label":"Your number of authorised vehicles",
     *     }
     * })
     * @Form\Type("Zend\Form\Element\Number")
     */
    public $permitsRequired = null;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     *
     */
    public $numVehicles;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     * @Form\Options({
     *     "label": "Current total vehicle authorization"
     * })
     *
     */
    public $numVehiclesLabel;



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
     *   "label": "ECMT Permit will only be used by vehicles that are Euro6 emissions compliant",
     *   "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *   "must_be_value": "Yes",
     *   "error-message": "error.messages.euro6"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $emissions = null;


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
     *     "label": "Cabotage will not be performed",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $cabotage = null;



    /**
     * @Form\Name("countryIds")
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
     *     "id" : "countryIds",
     *     "allowWrap":true,
     *     "multiple":"multiple",
     *     "empty": "Select options if applicable",
     *     "data-container-class": "form-control__container",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $countryIds = null;


    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *   "id" : "trips",
     *   "step" : "any"
     * })
     * @Form\Options({
     *     "label": "Number of trips abroad in the last 12 months",
     *     "short-label": "",
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Validator({"name":"Zend\Validator\Between", "options": {"min": -1, "max": 999999}})
     * @Form\Type("Zend\Form\Element\Number")
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
     *      "fieldset-attributes": {"id": "sector-list"},
     *      "fieldset-data-group": "sector-list",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "service_name": "Common\Service\Data\Sector"
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
}
