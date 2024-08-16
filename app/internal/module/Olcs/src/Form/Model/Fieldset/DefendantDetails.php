<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("defendant-details")
 * @Form\Type("\Common\Form\Elements\Types\EntitySearch")
 * @Form\Options({"label":"Defendant details"})
 */
class DefendantDetails
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Defendant type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "def_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $defendantType = null;

    /**
     * @Form\Attributes({"type":"submit","class":"govuk-button govuk-button--secondary","value":"Select"})
     * @Form\Options({
     *     "label": "Select"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $lookupTypeSubmit = null;
}
