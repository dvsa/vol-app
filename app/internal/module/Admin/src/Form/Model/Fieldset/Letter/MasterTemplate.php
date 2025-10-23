<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class MasterTemplate
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Name"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $name = null;

    /**
     * @Form\Options({
     *     "label": "Template Content",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"templateContent", "class":"extra-long", "name":"templateContent"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $templateContent = null;

    /**
     * @Form\Options({
     *     "label": "Is Default",
     *     "checked_value": "1",
     *     "unchecked_value": "0"
     * })
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"", "id":"isDefault"})
     */
    public $isDefault = null;

    /**
     * @Form\Options({"label": "Locale"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"small"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":5})
     */
    public $locale = null;
}