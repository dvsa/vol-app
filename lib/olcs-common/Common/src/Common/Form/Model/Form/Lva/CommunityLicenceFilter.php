<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true,"bypass_auth":true})
 * @Form\Name("lva-community-licence-filter")
 * @Form\Attributes({"method":"get", "class":"form__filter"})
 * @Form\Type("Common\Form\Form")
 */
class CommunityLicenceFilter
{
    /**
     * @Form\Options({"category": "com_lic_sts"})
     * @Form\Type("DynamicMultiCheckbox")
     */
    public $status;

    /**
     * @Form\Attributes({"value":1})
     * @Form\Type("Hidden")
     */
    public $isFiltered;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"govuk-button",
     *     "data-container-class":"js-hidden"
     * })
     * @Form\Options({
     *     "label": "lva-community-licence-filter-button"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter;
}
