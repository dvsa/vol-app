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
     *   "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "label": "I confirm that my ECMT permit(s) will only be used by vehicle(s) that are environmentally compliant to Euro 6 emissions standards.",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "Y"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
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
