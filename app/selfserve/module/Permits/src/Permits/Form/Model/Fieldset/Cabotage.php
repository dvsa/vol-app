<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Cabotage")
 */
class Cabotage
{

    /**
     * @Form\Name("WillCabotage")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--trips",
     * })
     * @Form\Options({
     *     "label": "",
     *     "label_attributes":{
     *          "class" : "form-control form-control--radio cabotageRadio"
     *     },
     *     "value_options":{
     *          "1" : "Yes",
     *          "0" : "No"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $willCabotage = null;

}

?>
