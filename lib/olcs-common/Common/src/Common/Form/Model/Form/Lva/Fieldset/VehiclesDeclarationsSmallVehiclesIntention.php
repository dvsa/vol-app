<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("smallVehiclesIntention")
 * @Form\Options({
 *     "label": "application_vehicle-safety_undertakings-smallVehiclesUndertakings",
 * })
 * @Form\Attributes({
 *     "class": "psv-show-small psv-show-both"
 * })
 */
class VehiclesDeclarationsSmallVehiclesIntention
{
    /**
     * @Form\Attributes({
     *     "id":"", "value":"markup-application_vehicle-safety_undertakings-smallVehiclesUndertakingsScotland"
     * })
     * @Form\Options({
     *     "label": "application_vehicle-safety_undertakings.smallVehiclesUndertakingsScotland.title"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $psvSmallVhlScotland;

    /**
     * @Form\Attributes({
     *     "value": "markup-application_vehicle-safety_undertakings-smallVehiclesUndertakings"
     * })
     * @Form\Options({
     *     "label": "application_vehicle-safety_undertakings.smallVehiclesUndertakings.title"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $psvSmallVhlUndertakings;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Required(false)
     * @Form\AllowEmpty(false)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({
     *     "label": "application_vehicle-safety_undertakings.smallVehiclesConfirmation",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "checked_value": "Y",
     *     "unchecked_value": "N"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $psvSmallVhlConfirmation;
}
