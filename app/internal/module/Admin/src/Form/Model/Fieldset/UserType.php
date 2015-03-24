<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 * @Form\Name("user-type")
 */
class UserType
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"type","placeholder":""})
     * @Form\Options({
     *     "label": "Type",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\UserTypesListDataService",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $type = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"roles","placeholder":"","multiple":"multiple", "class":"chosen-select-medium"})
     * @Form\Options({
     *     "label": "Roles",
     *     "disable_inarray_validator": false,
     *     "help-block": "Use CTRL to select multiple",
     *     "service_name": "Common\Service\Data\Role",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $roles = null;
}
