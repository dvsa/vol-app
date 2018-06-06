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
     * @Form\Attributes({"value":"","class":"visually-hidden"})
     * @Form\Options({
     *     "label":"Information Complete Date",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
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
     *                      "operator":"lte",
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
