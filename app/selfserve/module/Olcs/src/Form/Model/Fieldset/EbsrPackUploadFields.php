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
     * @Form\Name("submissionType")
     * @Form\Options({
     *     "label": "ebsr-upload-type",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "value_options":{
     *          "ebsrt_new":"ebsr-new",
     *          "ebsrt_refresh":"ebsr-refresh"
     *      }
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"submission_type","placeholder":"", "value":"ebsrt_new"})
     * @Form\Type("Radio")
     */
    public $submissionType;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleZipUpload")
     * @Form\Attributes({"id":"files"})
     */
    public $files = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Required(true)
     * @Form\Attributes({"required":false, "id":"uploadedFileCount"})
     * @Form\Type("Hidden")
     */
    public $uploadedFileCount = null;
}
