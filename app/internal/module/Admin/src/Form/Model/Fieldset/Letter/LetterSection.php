<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterSection
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Section Key"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    public $sectionKey = null;

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
     *     "label": "Section Type",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("Select")
     * @Form\Required(true)
     * @Form\Attributes({"id":"sectionType","class":"medium"})
     * @Form\Options({
     *     "value_options": {
     *         "letter_section_type_header": "Header",
     *         "letter_section_type_body": "Body",
     *         "letter_section_type_footer": "Footer"
     *     }
     * })
     */
    public $sectionType = null;

    /**
     * @Form\Options({
     *     "label": "Default Content",
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
     * @Form\Options({
     *     "label": "Goods or PSV",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Both"
     * })
     * @Form\Type("Select")
     * @Form\Required(false)
     * @Form\Attributes({"id":"goodsOrPsv","class":"medium"})
     * @Form\Options({
     *     "value_options": {
     *         "lcat_gv": "Goods",
     *         "lcat_psv": "PSV"
     *     }
     * })
     */
    public $goodsOrPsv = null;

    /**
     * @Form\Options({
     *     "label": "Northern Ireland Only",
     *     "checked_value": "1",
     *     "unchecked_value": "0"
     * })
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"", "id":"isNi"})
     */
    public $isNi = null;

    /**
     * @Form\Options({
     *     "label": "Requires Input",
     *     "checked_value": "1",
     *     "unchecked_value": "0"
     * })
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"", "id":"requiresInput"})
     */
    public $requiresInput = null;

    /**
     * @Form\Options({"label": "Minimum Length"})
     * @Form\Required(false)
     * @Form\Type("Number")
     * @Form\Attributes({"class":"short"})
     * @Form\Filter("Laminas\Filter\Digits")
     * @Form\Validator("Laminas\Validator\Digits")
     */
    public $minLength = null;

    /**
     * @Form\Options({"label": "Maximum Length"})
     * @Form\Required(false)
     * @Form\Type("Number")
     * @Form\Attributes({"class":"short"})
     * @Form\Filter("Laminas\Filter\Digits")
     * @Form\Validator("Laminas\Validator\Digits")
     */
    public $maxLength = null;

    /**
     * @Form\Options({
     *     "label": "Help Text",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("Textarea")
     * @Form\Attributes({"class":"extra-long", "rows": 3})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $helpText = null;
}
