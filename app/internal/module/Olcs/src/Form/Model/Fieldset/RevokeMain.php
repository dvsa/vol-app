<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class RevokeMain extends CaseBase
{
    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium", "multiple" : true})
     * @Form\Options({
     *     "label": "Select legislation",
     *     "service_name": "Olcs\Service\Data\PublicInquiryReason",
     *     "disable_inarray_validator": false,
     *     "use_groups":true
     * })
     * @Form\Type("DynamicSelect")
     */
    public $reasons = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"assignedCaseworker","class":"medium"})
     * @Form\Options({
     *     "label": "Assigned caseworker",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "service_name": "Olcs\Service\Data\UserListInternal",
     *     "use_groups": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $assignedCaseworker = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Agreed by",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $presidingTc = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "PTR agreed date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $ptrAgreedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Closed date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $closedDate = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Notes"
     * })
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $comment = null;
}
