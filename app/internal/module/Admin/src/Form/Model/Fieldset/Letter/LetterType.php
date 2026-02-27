<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterType
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
     *     "label": "Description",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("Textarea")
     * @Form\Attributes({"class":"extra-long", "rows": 5})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $description = null;

    /**
     * @Form\Options({
     *     "label": "Active",
     *     "checked_value": "1",
     *     "unchecked_value": "0"
     * })
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"", "id":"isActive"})
     */
    public $isActive = null;

    /**
     * @Form\Options({
     *     "label": "Master Template",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\Letter\MasterTemplate",
     *     "empty_option": "Please Select",
     *     "use_groups": false
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Attributes({"id":"masterTemplate","class":"medium"})
     */
    public $masterTemplate = null;

    /**
     * @Form\Options({
     *     "label": "Category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\Category",
     *     "context": {
     *       "isDocCategory": "Y"
     *     },
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Attributes({"id":"category","class":"medium"})
     */
    public $category = null;

    /**
     * @Form\Options({
     *     "label": "Sub Category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\SubCategory",
     *     "context": {
     *       "isDocCategory": "Y"
     *     },
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Attributes({"id":"subCategory","class":"medium"})
     */
    public $subCategory = null;

    /**
     * @Form\Options({
     *     "label": "Test Data",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\Letter\LetterTestData",
     *     "empty_option": "Please Select",
     *     "use_groups": false
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Attributes({"id":"letterTestData","class":"medium"})
     */
    public $letterTestData = null;

    /**
     * @Form\Options({
     *     "label": "Appendices",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\Letter\LetterAppendix",
     *     "use_groups": false
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Attributes({"id":"appendices","class":"medium chosen-select-large","multiple":"multiple"})
     */
    public $appendices = null;
}
