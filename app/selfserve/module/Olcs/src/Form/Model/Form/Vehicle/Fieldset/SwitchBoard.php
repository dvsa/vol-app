<?php

namespace Olcs\Form\Model\Form\Vehicle\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("options")
 * @Form\Attributes({
 *     "id":"radio"
 * })
 */
class SwitchBoard
{
    /**
     * @Form\Options({
     *     "label_attributes": {
     *         "class": "form-control form-control--radio form-control--advanced"
     *     },
     *     "disable_inarray_validator": true,
     *     "hint": "Select an option to manage your vehicles",
     *     "value_options": {
     *          "add": {
     *              "label": "licence.vehicle.switchboard.form.add.label",
     *              "value": "add",
     *              "attributes": {
     *                  "id":"add-vehicle"
     *              },
     *          },
     *          "remove": {
     *              "label": "licence.vehicle.switchboard.form.remove.label",
     *              "value": "remove",
     *              "attributes": {
     *                  "id":"remove-vehicle"
     *              },
     *          },
     *          "reprint": {
     *              "label": "licence.vehicle.switchboard.form.reprint.label",
     *              "value": "reprint",
     *              "attributes": {
     *                  "id":"reprint-vehicle"
     *              },
     *          },
     *          "transfer": {
     *              "label": "licence.vehicle.switchboard.form.transfer.label",
     *              "value": "transfer",
     *              "attributes": {
     *                  "id":"transfer-vehicle"
     *              },
     *          },
     *          "view": {
     *              "label": "licence.vehicle.switchboard.form.view.label",
     *              "value": "view",
     *              "attributes": {
     *                  "id":"view-vehicles"
     *              },
     *          },
     *          "view-removed": {
     *              "label": "licence.vehicle.switchboard.form.view.label-removed",
     *              "value": "view-removed",
     *              "attributes": {
     *                  "id":"view-removed-vehicles"
     *              },
     *          },
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *     options={
     *         "messages": {
     *             "isEmpty": "licence.vehicle.switchboard.form.error.select-option"
     *         },
     *         "break_chain_on_failure": true,
     *     }
     * )
     * @Form\Validator("Laminas\Validator\InArray",
     *     options={
     *         "haystack": {
     *             "add", "remove", "reprint", "transfer", "view", "view-removed"
     *         },
     *         "strict": true,
     *     }
     * )
     */
    public $options = null;
}
