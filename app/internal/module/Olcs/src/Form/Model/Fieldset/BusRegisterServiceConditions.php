<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-service-number-and-type")
 */
class BusRegisterServiceConditions
{
    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $table = null;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "TRC's / Conditions satisfactory",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"conditions[trcConditionChecked]",
     *      "value":"N"
     * })
     */
    public $trcConditionChecked;

    /**
     * @Form\Attributes({"class":"extra-long","id":"conditions[trcNotes]"})
     * @Form\Options({"label":"Notes"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $trcNotes = null;
}
