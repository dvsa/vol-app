<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("hoursOfWeek")
 * @Form\Type("Common\Form\Elements\Types\HoursPerWeek")
 */
class HoursOfWeek
{
    /**
     * @Form\ComposedObject("Common\Form\Model\Fieldset\HoursOfWeekContent")
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.hours-per-week-subtitle"
     * })
     */
    public $hoursPerWeekContent;
}
