<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Common\Form\Form")
 */
class DataRetentionExport
{
    /**
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\DataRetentionExportOptions")
     */
    public $exportOptions = null;


    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\GenerateActions")
     */
    public $formActions = null;
}
