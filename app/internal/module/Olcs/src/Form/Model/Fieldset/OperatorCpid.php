<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("operator-cpid")
 */
class OperatorCpid
{
    /**
     * @Form\Attributes({"id":"cpid","class":"inline"})
     * @Form\Options({
     *     "label": "internal-operator-profile-cpid",
     *     "empty_option": "Not set",
     *     "value": "defendant_type.operator",
     *     "disable_inarray_validator": false,
     *     "category": "operator_cpid"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $type = null;
}
