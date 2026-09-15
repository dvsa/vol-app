<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("dataTrafficArea")
 * @Form\Attributes({
 *      "class": "traffic-area"
 * })
 */
class TrafficArea
{
    /**
     * @Form\Attributes({"id":"trafficArea","placeholder":""})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation.dataTrafficArea.label.new",
     *     "hint" : "markup-traffic-area-help",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     * })
     * @Form\Type("\Laminas\Form\Element\Select")
     */
    public $trafficArea;

    /**
     *
     * @Form\Type("Common\Form\Elements\Types\TrafficAreaSet")
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation.dataTrafficArea.label.new",
     *     "label_attributes": {
     *         "class": "legend"
     *     },
     *     "hint" : "markup-traffic-area-help",
     *     "hint-position" : "below",
     * })
     */
    public $trafficAreaSet;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation.enforcementArea.label",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     * })
     * @Form\Type("\Laminas\Form\Element\Select")
     */
    public $enforcementArea;
}
