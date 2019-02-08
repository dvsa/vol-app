<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("IrhpPermitPrint")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class IrhpPermitPrint
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\IrhpPermitPrint")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\IrhpPermitPrintActions")
     */
    public $formActions = null;
}
