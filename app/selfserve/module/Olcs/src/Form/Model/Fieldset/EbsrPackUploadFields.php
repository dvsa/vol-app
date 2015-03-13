<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class EbsrPackUploadFields
{
    /**
     * @Form\Options({
     *     "label": "Upload type",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select an upload type",
     *     "service_name": "Common\Service\Data\EbsrSubTypeListDataService",
     *     "category": "ebsr_sub_type"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"submission_type","placeholder":"", "value":"ebsrt_new"})
     * @Form\Type("DynamicSelect")
     */
    public $submissionType;

    /**
     * @Form\Options({"label": "EBSR pack upload"})
     * @Form\Type("File")
     * @Form\Input("Zend\InputFilter\FileInput")
     * @Form\Filter({"name": "DecompressUploadToTmp"})
     * @Form\Validator({"name": "FileMimeType", "options":{"mimeType": "application/zip"}})
     */
    public $file;
}
