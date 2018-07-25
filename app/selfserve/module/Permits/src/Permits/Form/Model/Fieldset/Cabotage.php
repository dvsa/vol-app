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
     * @Form\Name("WontCabotage")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--cabotage",
     *   "id" : "WontCabotage",
     * })
     * @Form\Options({
     *   "checked_value": "Yes",
     *     "unchecked_value": "No",
     *     "label": "permits.form.cabotage.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "Yes"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $wontCabotage = null;
}
