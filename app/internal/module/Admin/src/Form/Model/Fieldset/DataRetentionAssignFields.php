<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("data_retention_assign_fields")
 */
class DataRetentionAssignFields
{
    /**
     * @Form\Attributes({"id":"assignedTo","placeholder":"",
     *     "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "Select a user",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\UserListInternal",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $assignedTo = null;
}
