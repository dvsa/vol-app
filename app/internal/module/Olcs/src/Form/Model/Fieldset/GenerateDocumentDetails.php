<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("details")
 */
class GenerateDocumentDetails
{
    /**
     * @Form\Attributes({"id": "category"})
     * @Form\Options({
     *     "label": "documents.data.category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\DocumentCategoryWithDocs",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Attributes({"id": "documentSubCategory"})
     * @Form\Options({
     *     "label": "documents.data.sub_category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\DocumentSubCategoryWithDocs",
     *     "empty_option": "Please select",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $documentSubCategory = null;

    /**
     * @Form\Attributes({"id": "documentTemplate"})
     * @Form\Options({
     *     "label": "documents.data.template",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $documentTemplate = null;
}
