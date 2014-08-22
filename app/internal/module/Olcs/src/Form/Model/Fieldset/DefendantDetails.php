<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     *     "help-block": "Please select a category",
     *     "category": "defendant_types"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $defendantType = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary small"})
     * @Form\Options({
     *     "label": "Submit",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $lookupTypeSubmit = null;


}

