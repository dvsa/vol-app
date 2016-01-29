<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("admin-cpms-report")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "Generate report"})
 */
class CpmsReport
{
    /**
     * @Form\Name("reportOptions")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\CpmsReportOptions")
     */
    public $reportOptions = null;


    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\GenerateActions")
     */
    public $formActions = null;
}
