<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("financial-standing-rate")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class FinancialStandingRate
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\FinancialStandingRate")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButtons")
     */
    public $formActions = null;
}
