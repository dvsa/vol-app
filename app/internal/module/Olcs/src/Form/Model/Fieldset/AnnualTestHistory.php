<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("annual-test-history-fields")
 * @Form\Options({"label":""})
 */
class AnnualTestHistory extends Base
{
    /**
     * @Form\Attributes({"id":"annualTestHistory","class":"extra-long","name":"annualTestHistory"})
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":4000})
     */
    public $annualTestHistory = null;
}
