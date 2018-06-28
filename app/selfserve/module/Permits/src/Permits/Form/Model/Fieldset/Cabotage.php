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
     *   "id" : "WillCabotage",
     *   "onClick" : "toggleGuidance()",
     * })
     * @Form\Options({
     *     "label": "",
     *     "label_attributes":{
     *          "class" : "form-control form-control--radio form-control--inline cabotageRadio"
     *     },
     *     "value_options":{
     *          "1" : "Yes",
     *          "0" : "No"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $willCabotage = null;

    /**
     * @Form\Name("Guidance")
     * @Form\Attributes({
     *     "value": "markup-interim-fee",
     *     "data-container-class": "guidance",
     *      "id" : "cabotage-hint",
     * })
     * @Form\Type("\Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $guidance = null;

}

?>
