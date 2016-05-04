<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("event-history-actions")
 */
class EventHistoryActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"cancel"})
     * @Form\Options({"label": "Close"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
