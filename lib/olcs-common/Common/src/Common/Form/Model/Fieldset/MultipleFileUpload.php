<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({
 *     "class": "file-uploader"
 * })
 */
class MultipleFileUpload
{
    /**
     * @Form\Required(false)
     * @Form\Type("Hidden")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator("Common\Validator\FileUploadCountV2", options={"min": 1})
     */
    public $fileCount;

    /**
     * @Form\Name("file")
     * @Form\Attributes({
     *   "class": "js-visually-hidden"
     * })
     * @Form\Options({
     *     "value": "common.file-upload.browse.title",
     *     "hint": "common.file-upload.browse.hint"
     * })
     * @Form\Type("\Common\Form\Elements\Types\AttachFilesButton")
     */
    public $controls;

    /**
     * @Form\Attributes({
     *   "class": "js-upload-list"
     * })
     * @Form\Options({"preview_images": "true"})
     * @Form\Type("\Common\Form\Elements\Types\FileUploadList")
     */
    public $list;

    /**
     * @Form\Name("__messages__")
     * @Form\Attributes({})
     * @Form\Options({})
     * @Form\Type("Hidden")
     */
    public $messages;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "inline-upload govuk-button js-upload",
     * })
     * @Form\Options({"label": "Upload"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $upload;
}
