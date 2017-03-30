<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("AnnualTestHistory")
 * @Form\Attributes({"method":"post"})
 */
class AnnualTestHistory
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":"","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\AnnualTestHistory")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
