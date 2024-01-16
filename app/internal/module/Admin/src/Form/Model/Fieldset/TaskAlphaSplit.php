<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Common\Form\Model\Form\Traits\VersionTrait;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("task-alpha-split")
 */
class TaskAlphaSplit
{
    use VersionTrait,
        IdTrait;

    /**
     * @Form\Attributes({"id":"user","placeholder":""})
     * @Form\Options({
     *     "short-label": "User",
     *     "label": "User",
     *     "service_name": "Olcs\Service\Data\UserListInternal",
     *     "empty_option": "Please select",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     */
    public $user = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({
     *      "short-label": "Task letters",
     *      "label":"Assign operator tasks starting with these letters"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":50})
     * @Form\Validator({
     *     "name": "Laminas\Validator\Regex",
     *     "options": {
     *         "pattern": "/^[a-zA-Z]+$/",
     *         "messages": {
     *             "regexNotMatch": "The input must contain only letters"
     *         }
     *     }
     * })
     */
    public $letters = null;
}
