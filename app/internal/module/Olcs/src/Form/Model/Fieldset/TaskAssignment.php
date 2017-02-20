<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("assignment")
 * @Form\Options({
 *     "label": "tasks.assignment",
 * })
 */
class TaskAssignment
{
    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"assignedToTeam","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.team",
     *     "service_name": "Olcs\Service\Data\Team",
     *     "empty_option": "please-select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $assignedToTeam = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"assignedToUser","placeholder":""})
     * @Form\Options({
     *     "label": "tasks.data.owner",
     *     "service_name": "Olcs\Service\Data\UserListInternal",
     *     "empty_option": "Unassigned"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $assignedToUser = null;
}
