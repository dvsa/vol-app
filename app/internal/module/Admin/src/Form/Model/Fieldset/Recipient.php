<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * Recipient form.
 */
class Recipient extends Base
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Recipient type",
     *      "value_options":{
     *          "N":"Subscriber",
     *          "Y":"Statutory Objector"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"id":"isObjector", "required":false})
     */
    public $isObjector = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"contactName","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"Contact Name"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":100})
     */
    public $contactName = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"email","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"Email"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     */
    public $emailAddress = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"A&D"})
     * @Form\Type("OlcsCheckbox")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "sendNoticesProcs",
     *          "context_values": {"","N"},
     *          "validators": {
     *              {
     *                  "name": "Identical",
     *                  "options": {
     *                      "token": "Y",
     *                      "messages": {
     *                          "notSame": "Subscription details must be selected"
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $sendAppDecision;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"N&P"})
     * @Form\Type("OlcsCheckbox")
     */
    public $sendNoticesProcs;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"trafficAreas","placeholder":"","multiple":"multiple", "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "Traffic area",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TrafficArea",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $trafficAreas = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Send police copy"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isPolice;
}
