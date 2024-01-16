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
     * @Form\Required(false)
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
     * @Form\Required(false)
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
     * @Form\Attributes({"value":"","class":"govuk-visually-hidden", "readonly":"true"})
     * @Form\Options({
     *     "label":"Information Complete Date",
     *     "create_empty_option": false,
     *     "render_delimiters": true
     *     })
     * @Form\Type("DateSelect")
     */
    public $informationCompleteDate;


    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"assignedDate"})
     * @Form\Options({
     *     "label": "Date first assigned to TC/DTC/TR",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+5",
     *     "min_year_delta": "-5"
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "informationCompleteDate",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {
     *                  "name": "Date",
     *                  "options": {
     *                      "format": "Y-m-d",
     *                      "messages": {
     *                          "dateInvalidDate": "Invalid Date"
     *                      }
     *                  },
     *                  "break_chain_on_failure": true,
     *              },
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "has_time": false,
     *                      "compare_to":"informationCompleteDate",
     *                      "operator":"gte",
     *                      "compare_to_label":"Information Complete Date"
     *                  }
     *              }
     *          }
     *      }
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $assignedDate =  null;



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
