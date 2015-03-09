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
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Upload type",
     *      "value_options":{
     *          "ebsrt_new":"EBSR new application",
     *          "ebsrt_refresh":"EBSR data refresh"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"submission_type",
     *      "value":"new_registration",
     *      "class":"field--list checkbox"
     * })
     */
    public $submissionType;

    /**
     * @Form\Options({"label": "EBSR pack upload"})
     * @Form\Type("File")
     * @Form\Input("Zend\InputFilter\FileInput")
     * @Form\Filter({"name": "DecompressUploadToTmp"})
     * @Form\Validator({"name": "FileMimeType", "options":{"mimeType": "application/zip"}})
     *
     * @Form\Attributes({
     *      "multiple":"true",
     * })
     */
    public $files;
}
