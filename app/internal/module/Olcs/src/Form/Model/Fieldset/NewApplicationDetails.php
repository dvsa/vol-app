<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("new-application-details")
 */
class NewApplicationDetails
{
    /**
     * @Form\Attributes({"id":"receivedDate"})
     * @Form\Options({
     *     "label": "Application received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Type("DateSelect")
     */
    public $receivedDate = null;

    /**
     * @Form\Attributes({"id":"trafficArea","placeholder":""})
     * @Form\Options({
     *     "label": "Traffic area",
     *     "disable_inarray_validator": false,
     * })
     * @Form\Type("Select")
     */
    public $trafficArea = null;
}
