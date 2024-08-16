<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-reg-ta")
*/
class BusRegTa extends BusRegDetails
{
    /**
     * @Form\Attributes({"id":"trafficAreas","placeholder":"","multiple":"multiple", "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "TAOs covered by route",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TrafficArea",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $trafficAreas = null;

    /**
     * @Form\Attributes({"id":"localAuthoritys","placeholder":"","multiple":"multiple", "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "Local authorities covered by route",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\LocalAuthority",
     *     "use_groups": "true"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $localAuthoritys = null;

    /**
     * @Form\Attributes({
     *      "id":"stoppingArrangements",
     *      "class":"extra-long",
     * })
     * @Form\Options({
     *     "label": "Stopping arrangements (Tours and excursions only)",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Type("Textarea")
     * @Form\Required(false)
     *
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     *
     * @Form\Validator({
     *      "name": "Laminas\Validator\StringLength",
     *      "options": {
     *          "min": 5,
     *          "max":800
     *      }
     * })
     */
    public $stoppingArrangements;
}
