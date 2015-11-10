<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("team-remove")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class TeamRemove
{
    /**
     * @Form\Name("team-remove-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TeamRemoveDetails")
     */
    public $teamRemoveDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\DeleteConfirmButtons")
     */
    public $formActions = null;
}
