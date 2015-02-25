<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\VersionTrait;

/**
 * Financial Standing Fieldset
 */
class FinancialStandingRate
{
    use VersionTrait;

    /**
     * @Form\Name("goodsOrPsv")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "id": "operator-type",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-type",
     *      "label": "financial-standing-rate-element-goodsOrPsv",
     *      "value_options":{
     *          "lcat_gv":"Goods",
     *          "lcat_psv":"PSV"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $goodsOrPsv = null;

    /**
     * @Form\Name("licenceType")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "id": "licence-type",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "licence-type",
     *      "label": "financial-standing-rate-element-licenceType",
     *      "value_options":{
     *          "ltyp_r": "Restricted",
     *          "ltyp_sn": "Standard National",
     *          "ltyp_si": "Standard International",
     *          "ltyp_sr": "Special Restricted"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $licenceType = null;

    /**
     * @Form\Name("firstVehicleRate")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "label": "financial-standing-rate-element-firstVehicleRate"
     * })
     * @Form\Type("Text")
     */
    public $firstVehicleRate = null;

    /**
     * @Form\Name("additionalVehicleRate")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "label": "financial-standing-rate-element-additionalVehicleRate"
     * })
     * @Form\Type("Text")
     */
    public $additionalVehicleRate = null;

    /**
     * @Form\Name("effectiveFrom")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *     "label": "financial-standing-rate-element-effectiveDate",
     *     "render_delimiters": false,
     *     "required": true,
     *     "max_year_delta": "+10",
     *     "min_year_delta": "-10"
     * })
     * @Form\Type("\Common\Form\Elements\Custom\DateSelect")
     */
    public $effectiveFrom = null;
}
