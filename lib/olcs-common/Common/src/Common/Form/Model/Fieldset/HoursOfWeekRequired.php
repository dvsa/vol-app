<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("hoursOfWeek")
 * @Form\Type("Common\Form\Elements\Types\HoursPerWeek")
 */
class HoursOfWeekRequired
{
    /**
     * @Form\Name("hoursPerWeekContent")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\HoursOfWeekContentRequired")
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.hours-per-week-subtitle",
     *     "hint":  "lva-tm-hours-per-week-hint",
     * })
     */
    public $hoursPerWeekContent;
}
