<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
* @codeCoverageIgnore Auto-generated file with no methods
* @Form\Name("bus-reg-ta")
*/
class BusRegTa extends Base
{
    /**
     * @Form\Attributes({"id":"trafficAreas","placeholder":"","multiple":"multiple"})
     * @Form\Options({
     *     "label": "TAOs covered by route",
     *     "disable_inarray_validator": false,
     *     "help-block": "Use CTRL to select multiple",
     *     "service_name": "Olcs\Service\Data\BusServiceType",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $trafficAreas = null;

    /**
     * @Form\Attributes({"id":"localAuths","placeholder":"","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Local authorities covered by route",
     *     "disable_inarray_validator": false,
     *     "help-block": "Use CTRL to select multiple",
     *     "service_name": "Olcs\Service\Data\BusServiceType",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $localAuths = null;

    /**
     * @Form\Attributes({
     *      "id":"stoppingArrangements",
     *      "class":"extra-long",
     * })
     * @Form\Options({
     *     "label": "Stopping arrangements (Tours and excursions only)",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     },
     *     "column-size": ""
     * })
     *
     * @Form\Type("Textarea")
     * @Form\Required(false)
     *
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     *
     * @Form\Validator({
     *      "name": "Zend\Validator\StringLength",
     *      "options": {
     *          "min": 5,
     *          "max":800
     *      }
     * })
     */
    public $stoppingArrangements;

}