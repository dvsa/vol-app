<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("ReportUpload")
 */
class ReportUpload
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Report type",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "category": "report_type"
     * })
     * @Form\Required(true)
     * @Form\Type("DynamicSelect")
     */
    public $reportType = null;

    /**
     * @Form\Options({"label":"File Upload"})
     * @Form\Type("\Zend\Form\Element\File")
     */
    public $file = null;
}
