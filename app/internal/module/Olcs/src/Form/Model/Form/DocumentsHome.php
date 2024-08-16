<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("documents-home")
 * @Form\Attributes({"method":"get", "class": "filters  form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class DocumentsHome
{
    /**
     * @Form\Options({
     *     "label": "documents-home.data.category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\DocumentCategory",
     *     "empty_option": "All",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Options({
     *     "label": "documents-home.data.sub_category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\DocumentSubCategory",
     *     "empty_option": "All",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $documentSubCategory = null;

    /**
     * @Form\Options({
     *     "label": "documents-home.data.source",
     *     "service_name": "staticList",
     *     "category": "document_types",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $isExternal = null;

    /**
     * @Form\Options({
     *     "label": "documents.filter.show-docs.title",
     *     "value_options": {
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Laminas\Form\Element\Select")
     */
    public $showDocs = null;

    /**
     * @Form\Options({
     *     "label": "documents.filter.format.title",
     *     "value_options": {
     *          "" : "All",
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Laminas\Form\Element\Select")
     */
    public $format = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "documents-home.submit.filter"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter = null;
}
