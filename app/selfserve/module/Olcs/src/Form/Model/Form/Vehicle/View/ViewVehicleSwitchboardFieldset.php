<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle\View;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Name("options")
 * @Form\Attributes({"id":"radio"})
 * @Form\Options({
 *     "radio-element": "options",
 *     "hint" : "licence.vehicle.view.switchboard.hint"
 * })
 */
class ViewVehicleSwitchboardFieldset
{
    const ATTR_OPTIONS = 'options';
    const ATTR_TRANSFER_CONTENT = 'transferContent';

    const RADIO_OPTION_REMOVE = 'remove';
    const RADIO_OPTION_REPRINT = 'reprint';
    const RADIO_OPTION_TRANSFER = 'transfer';

    /**
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"class": "govuk-radios--conditional", "data-module":"radios"}
     * })
     * @Form\Options({
     *     "label_attributes": {
     *         "class": "form-control form-control--radio form-control--advanced"
     *     },
     *     "value_options": {
     *          \Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboardFieldset::RADIO_OPTION_REMOVE: {
     *              "label": "licence.vehicle.view.switchboard.option.remove.label",
     *              "value": \Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboardFieldset::RADIO_OPTION_REMOVE,
     *              "attributes": {"id":"remove-vehicle"},
     *          },
     *          \Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboardFieldset::RADIO_OPTION_REPRINT: {
     *              "label": "licence.vehicle.view.switchboard.option.reprint.label",
     *              "value": \Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboardFieldset::RADIO_OPTION_REPRINT,
     *              "attributes": {"id":"reprint-vehicle"},
     *          },
     *          \Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboardFieldset::RADIO_OPTION_TRANSFER: {
     *              "label": "licence.vehicle.view.switchboard.option.transfer.label",
     *              "value": \Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboardFieldset::RADIO_OPTION_TRANSFER,
     *              "attributes": {"id":"transfer-vehicle"},
     *          },
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     * @Form\Validator("\Laminas\Validator\NotEmpty",
     *     options={
     *         "messages": {
     *             "isEmpty": "licence.vehicle.view.switchboard.error.is-empty"
     *         }
     *     }
     * )
     */
    public $options = null;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "disable_inarray_validator": false,
     *     "render-container": false,
     *     "label": "licence.vehicle.view.switchboard.option.transfer.select.label.plural",
     *     "label_attributes": {"class": "govuk-label"}
     * })
     * @Form\Attributes({
     *     "id": "select-a-licence",
     *     "class": "govuk-!-margin-right-2 govuk-select"
     * })
     * @Form\Type("Select")
     * @Form\Validator("\Laminas\Validator\Digits")
     * @Form\Validator("\Laminas\Validator\GreaterThan",
     *      options={
     *         "min": 0
     *     }
     * )
     */
    public $transferContent = null;
}
