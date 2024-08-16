<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("Laminas\Form\Fieldset")
 */
class ChecklistReminderFilter
{
    /**
     * @Form\Attributes({"id":"generate-continuation-date","placeholder":""})
     * @Form\Options({
     *     "label": "Date",
     *     "min_year_delta": "-5",
     *     "max_year_delta": "+5",
     *     "default_date": "now"
     * })
     * @Form\Type("MonthSelect")
     */
    public $date = null;
}
