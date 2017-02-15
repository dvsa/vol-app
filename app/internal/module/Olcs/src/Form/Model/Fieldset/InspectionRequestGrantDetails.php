<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Inspection Request Details
 *
 * @Form\Attributes({"class":"","id":"inspectionRequestGrantDetails"})
 * @Form\Name("inspection-request-grant-details")
 */
class InspectionRequestGrantDetails
{
    /**
     * @Form\Name("dueDate")
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "internal.inspection-request.form.due-date",
     *      "value_options":{
     *          "3":"internal.inspection-request.form.3-month",
     *          "6":"internal.inspection-request.form.6-month",
     *          "9":"internal.inspection-request.form.9-month",
     *          "12":"internal.inspection-request.form.12-month"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     */
    public $dueDate;

    /**
     * @Form\Name("caseworkerNotes")
     * @Form\Required(false)
     * @Form\Attributes({
     *      "id":"caseworkerNotes",
     *      "class":"long",
     *      "name":"caseworkerNotes",
     *      "required":false
     * })
     * @Form\Options({
     *     "label": "internal.inspection-request.form.caseworker-notes",
     *     "label_attributes": {
     *         "class": "long"
     *     }
     * })
     *
     * @Form\Type("TextArea")
     */
    public $caseworkerNotes = null;
}
