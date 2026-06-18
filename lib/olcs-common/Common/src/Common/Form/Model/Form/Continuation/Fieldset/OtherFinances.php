<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioHorizontal")
 */
class OtherFinances
{
    /**
     * @Form\Type("Common\Form\Elements\Types\RadioYesNo")
     * @Form\ErrorMessage("continuations.finances.otherFinances.error")
     */
    public $yesNo;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\OtherFinancesDetails")
     */
    public $yesContent;
}
