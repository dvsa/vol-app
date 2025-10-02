<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterIssue
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Issue Key"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    public $issueKey = null;

    /**
     * @Form\Options({"label": "Heading"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    public $heading = null;

    /**
     * @Form\Options({
     *     "label": "Category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\Category",
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     * @Form\Attributes({"id":"category","class":"medium"})
     */
    public $category = null;

    /**
     * @Form\Options({
     *     "label": "Sub Category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\SubCategory",
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Attributes({"id":"subCategory","class":"medium"})
     */
    public $subCategory = null;

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
     *     "label": "Default Body Content",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"defaultBodyContent", "class":"extra-long", "name":"defaultBodyContent"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $defaultBodyContent = null;

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

    /**
     * @Form\Options({
     *     "label": "Publish From",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateTimeSelect")
     * @Form\Filter("DateTimeSelectNullifier")
     * @Form\Validator("Date", options={"format": "Y-m-d H:i:s"})
     * @Form\Validator("DateTimeCompare", options={"min_operator": ">=", "compare_to":"now", "compare_to_label":"current date/time"})
     * @Form\Attributes({"id":"publishFrom"})
     */
    public $publishFrom = null;
}