<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmissionSendTo-fields")
 */
class SubmissionSendTo extends Base
{

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium js-sub-user",
     * "required":false})
     * @Form\Type("DynamicSelect")
     * @Form\Options({
     *     "label": "Send to",
     *     "service_name": "Olcs\Service\Data\UserInternalTeamList",
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
