<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Euro6Emissions")
 */
class Euro6Emissions
{

    /**
     * @Form\Name("MeetsEuro6")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *    "id" : "MeetsEuro6",
     *    "onClick" : "toggleGuidance()",
     * })
     * @Form\Options({
     *     "label": "",
     *     "label_attributes":{
     *          "class" : "block-label form-control form-control--radio form-control--inline euro6Radio"
     *     },
     *     "value_options":{
     *          "1" : "Yes",
     *          "0" : "No"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $meetsEuro6 = null;

    /**
     * @Form\Name("Guidance")
     * @Form\Attributes({
     *     "value": "markup-interim-fee",
     *     "data-container-class": "guidance",
     *      "id" : "euro6-hint",
     * })
     * @Form\Type("\Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $guidance = null;

}

?>
