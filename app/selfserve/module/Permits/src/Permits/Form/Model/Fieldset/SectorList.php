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
     * @Form\Options({
     *     "label": "markup-ecmt-sector-list-label",
     *     "fieldset-attributes": {"id": "sector-list"},
     *     "fieldset-data-group": "sector-list",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "service_name": "Common\Service\Data\Sector",
     *     "category": ""
     * })
     * @Form\Attributes({
     *   "class" : "input--trips",
     *    "id" : "EcmtSectorList",
     * })
     * @Form\Type("DynamicRadio")
     */
    public $busRegStatus;
}
