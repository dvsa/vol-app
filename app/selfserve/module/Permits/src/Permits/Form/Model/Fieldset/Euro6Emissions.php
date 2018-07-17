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
     *   "class" : "input--euro6",
     *    "id" : "MeetsEuro6",
     * })
     * @Form\Options({
     *   "checked_value": "Yes",
     *     "unchecked_value": "No",
     *     "label": "permits.form.euro6.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "Yes"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $meetsEuro6 = null;
}
