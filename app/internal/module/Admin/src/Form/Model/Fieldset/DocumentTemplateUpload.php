<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("DocumentTemplateUpload")
 */
class DocumentTemplateUpload
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"id":"templateFolder","placeholder":""})
     * @Form\Options({
     *     "label": "Template Folder",
     *     "value_options": {
     *          "root":"templates/",
     *          "gb":"templates/GB",
     *          "ni":"templates/NI",
     *          "image":"templates/Image",
     *          "guides":"guides/"
     *     },
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Required(true)
     * @Form\Type("\Laminas\Form\Element\Select")
     */
    public $templateFolder = null;


    /**
     * @Form\Attributes({"id":"category","placeholder":""})
     * @Form\Options({
     *     "label": "Category",
     *     "service_name": "Olcs\Service\Data\Category"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     */
    public $category = null;

    /**
     * @Form\Attributes({"id":"subCategory","placeholder":""})
     * @Form\Options({
     *     "label": "Sub category",
     *     "service_name": "Olcs\Service\Data\SubCategory"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     *
     */
    public $subCategory = null;

    /**
     * @Form\Name("description")
     * @Form\Options({
     *     "label": "Description"
     * })
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Form\Type("Text")
     */
    public $description = null;

    /**
     * @Form\Name("templateSlug")
     * @Form\Options({
     *     "label": "Template Slug Identifier (optional)",
     * })
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":100})
     * @Form\Type("Text")
     * @Form\Required(false)
     */
    public $templateSlug = null;

    /**
     * @Form\Options({"label":"File Upload"})
     * @Form\Type("\Laminas\Form\Element\File")
     */
    public $file = null;

    /**
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Supress From Op",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     */
    public $suppressFromOp = null;

    /**
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Is this a Northern Ireland template?",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     */
    public $isNi = null;
}
