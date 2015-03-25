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
     * @Form\AllowEmpty(false)
     * @Form\Required(true)
     * @Form\Attributes({"id":"userType","placeholder":"", "required":false})
     * @Form\Options({
     *     "label": "Type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\UserTypesListDataService",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $userType = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"team","placeholder":""})
     * @Form\Options({
     *     "label": "Team",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Team",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $team = null;

    /**
     * @Form\Options({"label":"Application Id"})
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"","required":false})
     * @Form\Type("Text")
     *
    public $application = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"team","placeholder":""})
     * @Form\Options({
     *     "label": "Transport managers",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TransportManager",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     *
    public $transportManager = null;
*/
    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"team","placeholder":""})
     * @Form\Options({
     *     "label": "Local authority",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\LocalAuthority",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $localAuthority = null;

    /**
     * @Form\Options({"label":"Licence number"})
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"","required":false})
     * @Form\Type("Text")
     */
    public $licenceNumber = null;

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
