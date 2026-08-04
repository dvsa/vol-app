<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioHorizontal")
 */
class LicenceChecklistConfirmation
{
    /**
     * @Form\Type("Common\Form\Elements\Types\RadioYesNo")
     * @Form\ErrorMessage("continuations.checklist.confirmation.error")
     */
    public $yesNo;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\LicenceChecklistConfirmationYes")
     */
    public $yesContent;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\LicenceChecklistConfirmationNo")
     */
    public $noContent;
}
