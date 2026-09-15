<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-vehicles-declarations-small-conditions")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class VehiclesDeclarationsSmallConditions
{
    /**
     * @Form\Options({
     *     "label": "application_vehicle-safety_undertakings.smallVehiclesUndertakingsScotland.title",
     *     "label_attributes": {"class": "govuk-heading-s"},
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $psvSmallVhlHeading;

    /**
     * @Form\Attributes({
     *     "id":"", "value":"markup-application_vehicle-safety_undertakings-smallVehiclesUndertakingsScotland"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $psvSmallVhlConditions;

    /**
     * @Form\Attributes({
     *     "value": "markup-application_vehicle-safety_undertakings-smallVehiclesUndertakings"
     * })
     * @Form\Options({
     *     "label": "application_vehicle-safety_undertakings.smallVehiclesUndertakings.title",
     *     "label_attributes": {"class": "govuk-heading-s"},
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $psvSmallVhlUndertakings;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "application_vehicle-safety_undertakings.smallVehiclesConfirmation",
     *     "label_attributes": {"class": "govuk-label govuk-checkboxes__label form-control form-control--checkbox form-control--advanced"},
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "must_be_value": "Y",
     *     "use_hidden_element": false,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $psvSmallVhlConfirmation;

    /**
     * @Form\Name("isOperatingSmallPsvAsPartOfLarge")
     * @Form\Type("Hidden")
     */
    public $isOperatingSmallPsvAsPartOfLarge;

    /**
     * @Form\Name("version")
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
