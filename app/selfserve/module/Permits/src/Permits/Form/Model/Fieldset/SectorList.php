<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SectorList")
 * @Form\Attributes({
 *     "class" : "sector-list guidance"
 * })
 */
class SectorList
{
    /**
     * @Form\Name("SectorList")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *    "id" : "EcmtLicence",
     * })
     * @Form\Options({
     *      "label": "markup-ecmt-sector-list-label",
     *      "fieldset-attributes": {"id": "sector-list"},
     *      "fieldset-data-group": "sector-list",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "value_options": {
     *          "Food products, beverages and tobacco, products of agriculture,
     *                      hunting and forests, fish and other fishing products",
     *          "Unrefined coal and lignite, crude petroleum and natural gas",
     *          "Textiles and textile products, leather and leather products",
     *      },
     * })
     * @Form\Type("Radio")
     */
    public $SectorList = null;

}
