<?php

namespace Olcs\Form\Model\Form\Vehicle\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class ConfirmFormActions
{
    /**
     * @Form\Attributes({
     *     "id": "next",
     *     "value": "Next",
     *     "class": "action--primary large",
     *     "title": "licence.vehicle.generic.button.next.title"
     * })
     * @Form\Type("Submit")
     */
    public $formActions = null;
}
