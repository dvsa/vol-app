<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterSectionVariantAdd
{
    /**
     * @Form\Type("Hidden")
     */
    public $sectionId = null;

    /**
     * @Form\Options({
     *     "label": "Vehicle Type",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Any"
     * })
     * @Form\Type("Select")
     * @Form\Required(false)
     * @Form\Attributes({"id":"goodsOrPsv","class":"medium"})
     * @Form\Options({
     *     "value_options": {
     *         "lcat_gv": "Goods Vehicle",
     *         "lcat_psv": "PSV"
     *     }
     * })
     */
    public $goodsOrPsv = null;

    /**
     * @Form\Options({
     *     "label": "Application Type",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Any"
     * })
     * @Form\Type("Select")
     * @Form\Required(false)
     * @Form\Attributes({"id":"isVariation","class":"medium"})
     * @Form\Options({
     *     "value_options": {
     *         "0": "New Application",
     *         "1": "Variation"
     *     }
     * })
     */
    public $isVariation = null;

    /**
     * @Form\Options({
     *     "label": "NI / GB",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Any"
     * })
     * @Form\Type("Select")
     * @Form\Required(false)
     * @Form\Attributes({"id":"isNi","class":"medium"})
     * @Form\Options({
     *     "value_options": {
     *         "0": "GB",
     *         "1": "NI"
     *     }
     * })
     */
    public $isNi = null;

    /**
     * @Form\Options({
     *     "label": "Organisation Type",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Any"
     * })
     * @Form\Type("Select")
     * @Form\Required(false)
     * @Form\Attributes({"id":"organisationType","class":"medium"})
     * @Form\Options({
     *     "value_options": {
     *         "org_t_st": "Sole Trader",
     *         "org_t_rc": "Registered Company (LTD)",
     *         "org_t_llp": "LLP",
     *         "org_t_p": "Partnership",
     *         "org_t_pa": "Other"
     *     }
     * })
     */
    public $organisationType = null;

    /**
     * @Form\Options({
     *     "label": "Letter Choice",
     *     "disable_inarray_validator": false,
     *     "empty_option": "None"
     * })
     * @Form\Type("Select")
     * @Form\Required(false)
     * @Form\Attributes({"id":"letterChoice","class":"medium"})
     * @Form\Options({
     *     "value_options": {}
     * })
     */
    public $letterChoice = null;
}
