<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-decision-terminate")
 */
class LicenceStatusDecisionTerminate
{
    /**
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\Options({
     *     "label": "licence-status.terminate.date.label",
     *      "create_empty_option": true,
     *      "min_year_delta": "-5",
     * })
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name":"\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $terminateDate = null;
}
