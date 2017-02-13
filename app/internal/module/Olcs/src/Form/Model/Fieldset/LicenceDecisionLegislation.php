<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-decision-legislation")
 */
class LicenceDecisionLegislation
{
    /**
     * @Form\Attributes({
     *      "id":"reasons","placeholder":"",
     *      "class":"chosen-select-medium",
     *      "multiple" : true
     * })
     * @Form\Options({
     *     "label": "Legislation",
     *     "service_name": "Olcs\Service\Data\LicenceDecisionLegislation",
     *     "disable_inarray_validator": false,
     *     "use_groups":true
     * })
     * @Form\Type("DynamicSelect")
     */
    public $decisions = null;
}
