<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("admin-permits-report")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "Generate report"})
 */
class PermitsReport
{
    /**
     * @Form\Name("reportOptions")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\PermitsReportOptions")
     */
    public $reportOptions = null;


    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\GenerateActions")
     */
    public $formActions = null;
}
