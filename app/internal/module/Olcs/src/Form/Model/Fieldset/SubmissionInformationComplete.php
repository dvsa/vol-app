<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmissionInformationComplete-fields")
 */
class SubmissionInformationComplete extends Base
{

    /**
     * @Form\Options({
     *     "label": "Information completed date",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $informationCompleteDate = null;
}
