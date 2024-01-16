<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("details")
 * @Form\Options({"label":"documents.details"})
 */
class Details
{
    /**
     * @Form\Attributes({"id":"category","placeholder":""})
     * @Form\Options({
     *     "label": "documents.data.category",
     *     "service_name": "Olcs\Service\Data\DocumentCategory",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Attributes({"id":"documentSubCategory","placeholder":""})
     * @Form\Options({
     *     "label": "documents.data.sub_category",
     *     "service_name": "Olcs\Service\Data\DocumentSubCategory",
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $documentSubCategory = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"documents.data.description"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":255})
     */
    public $description = null;

    /**
     * @Form\Options({"label":"documents.data.file"})
     * @Form\Type("\Laminas\Form\Element\File")
     */
    public $file = null;
}
