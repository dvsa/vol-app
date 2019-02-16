<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("reportUpload")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "Upload report"})
 */
class ReportUpload
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\ReportUpload")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\ReportUploadActions")
     */
    public $formActions = null;
}
