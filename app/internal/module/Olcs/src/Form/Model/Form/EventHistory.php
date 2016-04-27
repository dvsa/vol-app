<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("event-history")
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class EventHistory
{
    /**
     * @Form\Name("event-history-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\EventHistoryDetails")
     */
    public $eventHistoryDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\EventHistoryActions")
     */
    public $formActions = null;
}
