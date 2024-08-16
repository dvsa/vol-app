<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("Common\Form\Form")
 * @Form\Name("data-retention-admin")
 */
class DataRetentionAdmin
{
    /**
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\DataRetentionRuleDetails")
     */
    public $ruleDetails = null;


    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormEditCrudActions")
     */
    public $formActions = null;
}
