<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmissionSendTo-fields")
 */
class SubmissionSendTo extends Base
{
    /**
     * @Form\Name("tcOrOther")
     * @Form\Attributes({"id": "tcOrOther","required":true})
     * @Form\Options({
     *     "label": "Assign to:",
     *      "fieldset-attributes": {
     *          "id": "fieldset-tc-or-other",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-type",
     *      "value_options":{
     *          "tc":"TC/DTC",
     *          "other":"Other User"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $tcOrOther = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"presidingTcUser","data-container-class":"tcUser js-hidden","placeholder":"","class":"chosen-select-medium js-sub-user",
     * "required":false})
     * @Form\Type("DynamicSelect")
     * @Form\Options({
     *     "label": "TC/DTC",
     *     "disable_inarray_validator": true,
     *     "use_groups":true
     * })
     */
    public $presidingTcUser = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"recipientUser","data-container-class":"otherUser js-hidden","placeholder":"","class":"chosen-select-medium js-sub-user",
     * "required":false})
     * @Form\Type("DynamicSelect")
     * @Form\Options({
     *     "label": "Other User",
     *     "service_name": "Olcs\Service\Data\UserListInternalExcludingLimitedReadOnlyUsersSorted",
     *     "disable_inarray_validator": true,
     *     "use_groups":true
     * })
     */
    public $recipientUser = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $senderUser = null;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Urgent?",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $urgent;
}
