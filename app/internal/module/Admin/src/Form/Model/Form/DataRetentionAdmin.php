<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Common\Form\Form")
 */
class DataRetentionAdmin
{
    /**
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\DataRetentionRuleDetails")
     */
    public $ruleDetails = null;


    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormEditCrudActions")
     */
    public $formActions = null;
}
