<?php

namespace Common\Form\Model\Form\Continuation;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\InsufficientFinancesForm")
 */
class InsufficientFinances
{
    /**
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\InsufficientFinancesSummary")
     */
    public $insufficientFinancesSummary;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\InsufficientFinances")
     */
    public $insufficientFinances;
}
