<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("details")
 */
class TaskDetails
{
    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Options({
     *     "label": "tasks.data.link",
     *     "disable_html_escape": true
     * })
     */
    public $link = null;

    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Options({
     *     "label": "tasks.data.status",
     *     "disable_html_escape": true
     * })
     */
    public $status = null;

    /**
     * @Form\Options({
     *     "label": "tasks.data.actionDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "required": true,
     *     "min_year_delta": "-10",
     *     "max_year_delta": "+10"
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Filter("DateSelectNullifier"})
     */
    public $actionDate = null;

    /**
     * @Form\Options({
     *     "checked_value": "Y",
     *     "unchecked_value":"N",
     *     "label": "tasks.data.urgent"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $urgent = null;

    /**
     * @Form\Attributes({"id":"category","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.category",
     *     "service_name": "Olcs\Service\Data\Category",
     *     "context": {"isTaskCategory": "Y" },
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Attributes({"id":"subCategory","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.sub_category",
     *     "service_name": "Olcs\Service\Data\SubCategory",
     *     "context": {"isTaskCategory": "Y" },
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $subCategory = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({
     *     "label": "tasks.data.description"
     * })
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":255})
     */
    public $description = null;
}
