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
     *     "label": "Business type",
     *     "value": "defendant_type.operator",
     *     "disable_inarray_validator": false,
     *     "service_name": "staticList",
     *     "category": "business_types"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $type = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"refresh"})
     * @Form\Options({
     *     "label": "Refresh",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $refresh = null;

    /**
     * @Form\Attributes({"id":"typeChanged"})
     * @Form\Type("Hidden")
     */
    public $typeChanged = null;
}
