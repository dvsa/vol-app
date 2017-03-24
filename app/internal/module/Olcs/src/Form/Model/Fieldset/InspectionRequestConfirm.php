<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Inspection Reauest Confirm
 * 
 * @Form\Attributes({"class":""})
 * @Form\Name("inspection-request-confirm")
 */
class InspectionRequestConfirm
{
    /**
     * @Form\Name("createInspectionRequest")
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "internal.inspection-request.form.create-inspection-request",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "value": "Y",
     * })
     */
    public $createInspectionRequest;
}
