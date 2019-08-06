<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("DocTemplateFilter")
 * @Form\Attributes({"method":"get", "class": "filters  form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class DocTemplateFilter
{
    /**
     * @Form\Options({
     *     "label": "Category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\DocumentCategory",
     *     "empty_option": "All",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "documents-home.submit.filter"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;
}
