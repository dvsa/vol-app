<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioHorizontal")
 */
class FinancesFactoring
{
    /**
     * @Form\Type("Common\Form\Elements\Types\RadioYesNo")
     * @Form\ErrorMessage("continuations.finances.factoring.error")
     */
    public $yesNo;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\FactoringDetails")
     */
    public $yesContent;
}
