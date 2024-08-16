<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("ReportUpload")
 */
class ReportUpload
{
    /**
     * @Form\Attributes({"id":"reportType","placeholder":""})
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
     * @Form\Attributes({"id":"docTemplate","disabled": true,"data-container-class":"docTemplateContainer js-hidden"})
     * @Form\Options({
     *     "label": "Letter Template",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\ReportLetterTemplate"
     *
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $docTemplate = null;

    /**
     * @Form\Attributes({"id":"emailTemplate","disabled": true,"data-container-class":"emailTemplateContainer js-hidden"})
     * @Form\Options({
     *     "label": "Email Template",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\ReportEmailTemplate"
     *
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $emailTemplate = null;

    /**
     * @Form\Options({"label":"File Upload"})
     * @Form\Type("\Laminas\Form\Element\File")
     */
    public $file = null;
}
