<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("operator-business-type")
 */
class OperatorBusinessType
{
    /**
     * @Form\Attributes({"id":"businessType","class":"inline"})
     * @Form\Options({
     *     "label": "internal-operator-profile-business-type",
     *     "value": "defendant_type.operator",
     *     "disable_inarray_validator": false,
     *     "category": "org_type"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $type = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary js-hidden","id":"refresh"})
     * @Form\Options({
     *     "label": "internal-operator-profile-business-type-refresh"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $refresh = null;
}
