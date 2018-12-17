<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("IrhpPermitPrintFilter")
 * @Form\Attributes({"method":"get","class":"filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrhpPermitPrintFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Validity dates",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\IrhpPermitPrintStock"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $irhpPermitStock = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "filter-button"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;
}
