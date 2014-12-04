<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-service-number-and-type")
 */
class BusRegisterServiceTimetable extends Base
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Timetable acceptable",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"isAcceptable",
     *      "value":"N"
     * })
     */
    public $isAcceptable;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Maps supplied",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"mapsSupplied",
     *      "value":"N"
     * })
     */
    public $mapsSupplied;

    /**
     * @Form\Attributes({"class":"extra-long","id":"otherDetails"})
     * @Form\Options({"label":"Route description"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":800}})
     */
    public $routeDescription = null;
}
