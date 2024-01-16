<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("public-holiday")
 */
class PublicHoliday
{
    use IdTrait;

    /**
     * @Form\Type("MultiCheckbox")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "id":"areas",
     *     "name": "areas",
     * })
     * @Form\Options({
     *     "label": "Area:",
     *     "value_options": {
     *         "isEngland": "England",
     *         "isWales": "Wales",
     *         "isScotland": "Scotland",
     *         "isNi": "Northern Ireland",
     *     },
     * })
     */
    public $areas;

    /**
     * @Form\Type("DateSelect")
     * @form\Required(true)
     * @Form\Options({
     *     "label": "Holiday Date:",
     *      "create_empty_option": true,
     *      "max_year_delta": "+5",
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     */
    public $holidayDate = null;
}
