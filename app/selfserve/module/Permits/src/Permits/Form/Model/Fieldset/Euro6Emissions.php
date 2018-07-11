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
     *      "label": "Choose one licence below",
     *      "fieldset-attributes": {"id": "ecmt-licence"},
     *      "fieldset-data-group": "licence-type",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "value_options": {
     *          "OB2013691 (North East)",
     *          "OC010019897 (North West)",
     *          "PB5553691 (South East)",
     *          "PC010119896 (South West)",
     *      },
     * })
     * @Form\Type("Radio")
     */

    public $meetsEuro6 = null;
}
