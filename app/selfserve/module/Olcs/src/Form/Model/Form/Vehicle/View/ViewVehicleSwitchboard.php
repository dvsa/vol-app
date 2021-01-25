<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle\View;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("view-vehicle-switch-board-form")
 * @Form\Type("\Common\Form\Form")
 */
class ViewVehicleSwitchboard
{
    const FIELD_OPTIONS_FIELDSET_NAME = 'optionsFieldset';
    const FIELD_OPTIONS_NAME = 'options';

    /**
     * @Form\Name("optionsFieldset")
     * @Form\Options({
     *     "label": "licence.vehicle.view.switchboard.header",
     *     "label_attributes": {"class": "govuk-fieldset__legend govuk-fieldset__legend--l"}
     *     })
     * @Form\ComposedObject("Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboardFieldset")
     */
    public $optionsFieldset = null;

    /**
     * @Form\Attributes({
     *     "id": "next",
     *     "value": "licence.vehicle.view.switchboard.action.next.label",
     *     "class": "action--secondary large",
     *     "title": "licence.vehicle.view.switchboard.action.next.title"
     * })
     * @Form\Type("Submit")
     */
    public $formActions = null;
}
