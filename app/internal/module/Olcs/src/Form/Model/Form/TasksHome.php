<?php

namespace Olcs\Form\Model\Form;

use Common\Form\Elements\InputFilters\Checkbox;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("tasks-home")
 * @Form\Attributes({"method":"get", "class": "filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class TasksHome
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.team",
     *     "value_options": {
     *
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Laminas\Form\Element\Select")
     */
    public $assignedToTeam = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.owner",
     *     "value_options": {
     *
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Laminas\Form\Element\Select")
     */
    public $assignedToUser = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\TaskCategory",
     *     "empty_option": "All",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.sub_category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\TaskSubCategory",
     *     "empty_option": "All",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $taskSubCategory = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.date",
     *     "disable_inarray_validator": false,
     *     "category": "task_date_types"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $date = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.status",
     *     "disable_inarray_validator": false,
     *     "category": "task_status_types"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;

    /**
     * @Form\Options({
     *     "label": "documents.filter.show-tasks.title",
     *     "value_options": {
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Laminas\Form\Element\Select")
     */
    public $showTasks = null;

    /**
     * @Form\Options({
     * "label": "tasks.data.messages",
     * "must_be_checked": true
     * })
     * @Form\Type(\Common\Form\Elements\InputFilters\Checkbox::class)
     */
    public ?Checkbox $messaging = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.urgent",
     *     "value_options": {
     *
     *     },
     *     "must_be_checked": true
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\Checkbox")
     */
    public $urgent = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "tasks.submit.filter"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $sort = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $order = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $limit = null;
}
