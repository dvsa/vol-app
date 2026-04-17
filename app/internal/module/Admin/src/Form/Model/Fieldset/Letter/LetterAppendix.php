<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterAppendix
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Appendix Key"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    public $appendixKey = null;

    /**
     * @Form\Options({"label": "Name"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    public $name = null;

    /**
     * @Form\Options({
     *     "label": "Description",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("Textarea")
     * @Form\Attributes({"class":"extra-long", "rows": 3})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $description = null;

    /**
     * @Form\Options({
     *     "label": "Appendix Type",
     *     "value_options": {
     *         "pdf": "PDF (Append As-Is)",
     *         "editable": "Editable Content"
     *     }
     * })
     * @Form\Type("Select")
     * @Form\Required(true)
     * @Form\Attributes({"id":"appendixType","class":"medium"})
     */
    public $appendixType = null;

    /**
     * @Form\Options({
     *     "label": "Default Content (for Editable type)",
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

    /**
     * @Form\Options({"label": "PDF Document"})
     * @Form\Required(false)
     * @Form\Type("\Laminas\Form\Element\File")
     * @Form\Attributes({"id":"document","accept":".pdf"})
     */
    public $document = null;
}
