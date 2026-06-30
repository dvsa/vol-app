<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("evidence")
 * @Form\Attributes({"class":"last"})
 */
class FinancialEvidenceEvidence
{
    /**
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({
     *     "fieldset-attributes": {
     *         "id": "files",
     *     },
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false, "id":"uploadedFileCount"})
     * @Form\Type("Hidden")
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "uploadNowRadio",
     *          "context_values": {"1"},
     *          "validators": {
     *              {
     *                  "name": "\Common\Validator\FileUploadCount",
     *                  "options": {
     *                      "min": 1,
     *                      "message": "lva-financial-evidence-upload.required"
     *                  }
     *              }
     *          }
     *      }
     * )
     */
    public $uploadedFileCount;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"uploadNowRadio","allowWrap":true,"data-container-class":"form-control__container"})
     * @Form\Options({
     *     "label": "lva-financial-evidence-upload-now.label",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "value_options": {\Common\RefData::AD_UPLOAD_NOW:"lva-financial-evidence-upload-now.yes"},
     *     "error-message": "financialEvidence_uploadNow-error",
     *     "single-radio": true
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $uploadNowRadio;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Attributes({"id":"files", "class":"file-uploader"})
     */
    public $files;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"uploadLaterRadio","allowWrap":true,"data-container-class":"form-control__container"})
     * @Form\Options({
     *     "label": "lva-financial-evidence-upload-now.label",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "value_options": {\Common\RefData::AD_UPLOAD_LATER:"lva-financial-evidence-upload-now.later"},
     *     "error-message": "financialEvidence_uploadNow-error",
     *     "single-radio": true
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $uploadLaterRadio;

    /**
     * @Form\Attributes({
     *     "id":"uploadLaterMessage",
     *     "data-container-class": "upload-later last",
     *     "value": "markup-financial-evidence-upload-later",
     *     "class": "upload-later-message"
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Name("uploadLaterMessage")
     */
    public $uploadLater;
}
