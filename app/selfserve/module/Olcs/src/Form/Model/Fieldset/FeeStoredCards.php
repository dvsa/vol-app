<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("fee-stored-cards")
 */
class FeeStoredCards
{
    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "form.fee-stored-cards.label",
     * })
     * @Form\Type("Select")
     */
    public $card = null;
}
