<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("user-details")
 */
class UserDetails
{
    /**
     * @Form\Attributes({"id":"team","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Team",
     *     "service_name": "Olcs\Service\Data\Team",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     *
     * @Form\Type("DynamicSelect")
     */
    public $team = null;
}
