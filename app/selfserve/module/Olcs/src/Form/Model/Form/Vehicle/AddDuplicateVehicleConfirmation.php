<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("add-duplicate-vehicle-confirmation-form")
 *
 * @Form\Type("\Common\Form\Form")
 */
class AddDuplicateVehicleConfirmation
{
    public const FIELD_OPTIONS_FIELDSET_NAME = 'optionsFieldset';
    public const FIELD_OPTIONS_NAME = 'options';

    /**
     * @Form\Name("optionsFieldset")
     * @Form\ComposedObject("Olcs\Form\Model\Form\Vehicle\Fieldset\YesNo")
     */
    public $optionsFieldset = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Vehicle\Fieldset\ConfirmFormActions")
     */
    public $formActions = null;
}
