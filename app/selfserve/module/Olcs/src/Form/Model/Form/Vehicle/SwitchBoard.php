<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("switch-board-form")
 * @Form\Type("\Common\Form\Form")
 */
class SwitchBoard
{
    public const FIELD_OPTIONS_FIELDSET_NAME = 'optionsFieldset';

    public const FIELD_OPTIONS_NAME = 'options';
    public const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD = 'add';
    public const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE = 'remove';
    public const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER = 'transfer';
    public const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT = 'reprint';
    public const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW = 'view';
    public const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED = 'view-removed';

    /**
     * @Form\Name("optionsFieldset")
     * @Form\ComposedObject("Olcs\Form\Model\Form\Vehicle\Fieldset\SwitchBoard")
     */
    public $optionsFieldset = null;

    /**
     * @Form\Options({
     *     "label": "Next",
     * })
     * @Form\Attributes({
     *     "id": "next",
     *     "title": "licence.vehicle.switchboard.form.next.title",
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Type("Button")
     */
    public $formActions = null;
}
