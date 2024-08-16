<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("fields")
 */
class DelayItem
{
    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":"nextReviewDate", "data-container-class": "nextReviewDate"})
     * @Form\Options({
     *     "label": "Next review date:",
     *      "create_empty_option": true,
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $nextReviewDate = null;
}
