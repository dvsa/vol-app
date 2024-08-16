<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * IrhpPermitPrint form.
 */
class IrhpPermitPrint
{
    /**
     * @Form\Attributes({"id":"irhpPermitType","placeholder":""})
     * @Form\Options({
     *     "label": "Permit type",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\IrhpPermitPrintType"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $irhpPermitType = null;

    /**
     * @Form\Attributes({"id":"irhpPermitTypeForCountry","placeholder":""})
     * @Form\Type("Hidden")
     */
    public $irhpPermitTypeForCountry = null;

    /**
     * @Form\Attributes({"id":"irhpPermitTypeForStock","placeholder":""})
     * @Form\Type("Hidden")
     */
    public $irhpPermitTypeForStock = null;

    /**
     * @Form\Attributes({"id":"country","placeholder":""})
     * @Form\Options({
     *     "label": "Country",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": true
     * })
     * @Form\Required(false)
     * @Form\Type("Select")
     */
    public $country = null;

    /**
     * @Form\Attributes({"id":"irhpPermitStock","placeholder":""})
     * @Form\Options({
     *     "label": "Validity dates / Stock",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": true
     * })
     * @Form\Type("Select")
     */
    public $irhpPermitStock = null;

    /**
     * @Form\Attributes({"id":"irhpPermitStockForRangeType","placeholder":""})
     * @Form\Type("Hidden")
     */
    public $irhpPermitStockForRangeType = null;

    /**
     * @Form\Attributes({"id":"irhpPermitRangeType","placeholder":""})
     * @Form\Options({
     *     "label": "Bilateral permit category",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": true
     * })
     * @Form\Required(false)
     * @Form\Type("Select")
     */
    public $irhpPermitRangeType = null;
}
