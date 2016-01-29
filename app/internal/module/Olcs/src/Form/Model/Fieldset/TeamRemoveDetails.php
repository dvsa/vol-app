<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("team-remove-details")
 */
class TeamRemoveDetails
{
    /**
     * @Form\Attributes({"id":"assignedToTeam","placeholder":""})
     * @Form\Options({
     *     "label": "Reassign tasks to",
     *     "service_name": "Olcs\Service\Data\Team",
     *     "empty_option": "Please select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $newTeam = null;
}
