<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterSectionVariantEdit
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

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
     *     "service_name": "Olcs\Service\Data\Letter\LetterChoice",
     *     "empty_option": "None",
     *     "use_groups": false
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Attributes({"id":"letterChoice","class":"medium"})
     */
    public $letterChoice = null;

    /**
     * @Form\Options({
     *     "label": "Default Content",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"defaultContent", "class":"extra-long", "name":"defaultContent"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $defaultContent = null;
}
