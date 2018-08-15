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
     * @Form\Type("Zend\Form\Element\Hidden")
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     * @Form\Options({
     *     "label": "Total Authorized Vehicles"
     * })
     *
     */
    public $numVehiclesLabel;


    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     * @Form\Options({
     *     "label": "Total Authorized Vehicles"
     * })
     *
     */
    public $numVehicles;



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
     * @Form\Validator({"name":"Zend\Validator\GreaterThan", "options": {"min": 1}})
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
     * @Form\Attributes({"id":"","placeholder":"","multiple":"multiple","class":"chosen-select-large"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Restricted countries",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country",
     *     "use_groups": false
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $countrys = null;


    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *   "id" : "trips",
     *   "step" : "any"
     * })
     * @Form\Options({
     *     "label": "Number of trips abroad",
     *     "short-label": "",
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Validator({"name":"Zend\Validator\Between", "options": {"min": 1, "max": 999999}})
     * @Form\Type("Zend\Form\Element\Number")
     */
    public $trips = null;



    /**
     * @Form\Name("internationalJourneys")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--international-journey",
     *    "id" : "internationalJourneyList",
     * })
     * @Form\Options({
     *      "label": "Percentage of International Journeys",
     *      "fieldset-attributes": {"id": "international_journeys"},
     *      "fieldset-data-group": "international_journeys",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "value_options": {
     *          "less.than.60%",
     *          "from.60%.to.90%",
     *          "more.than.90%",
     *      },
     *      "error-message": "error.messages.international-journey"
     * })
     * @Form\Type("Radio")
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
     *      "value_options": {
     *          "Food products, beverages and tobacco, products of agriculture,
     *                      hunting and forests, fish and other fishing products",
     *          "Unrefined coal and lignite, crude petroleum and natural gas",
     *          "Textiles and textile products, leather and leather products",
     *      },
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
