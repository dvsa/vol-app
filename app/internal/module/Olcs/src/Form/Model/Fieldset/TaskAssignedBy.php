<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("assignedBy")
 * @Form\Options({
 *     "label": "tasks.assignedBy",
 * })
 */
class TaskAssignedBy
{
    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Options({
     *     "label": "tasks.assignedBy.user",
     * })
     */
    public $assignedByUserName = null;
}
