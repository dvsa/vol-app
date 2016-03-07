<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-decision-reasons")
 */
class LicenceStatusDecisionReasons
{
    /**
     * @Form\Attributes({
     *      "id":"reasons","placeholder":"",
     *      "class":"chosen-select-medium",
     *      "multiple" : true
     * })
     * @Form\Options({
     *     "label": "Legislation",
     *     "service_name": "Olcs\Service\Data\PublicInquiryReason",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select applicable legislation",
     *     "use_groups":true
     * })
     * @Form\Type("DynamicSelect")
     */
    public $reasons = null;
}
