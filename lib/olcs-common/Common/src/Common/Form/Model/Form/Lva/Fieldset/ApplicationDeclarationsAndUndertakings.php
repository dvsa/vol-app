<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("declarations")
 */
class ApplicationDeclarationsAndUndertakings
{
    /**
     * @Form\Attributes({"value": "markup-review-text"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $review;

    /**
     * @Form\Attributes({"value": "markup-declaration-text"})
     * @Form\Type("Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $declaration;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "application.signature.options.label",
     *     "label_attributes": {
     *         "class":"form-control form-control--radio form-control--advanced"
     *     },
     *     "value_options": {"Y": "application.signature.options.verify", "N": "application.signature.options.sign"},
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $signatureOptions;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label_attributes": {
     *         "class":"form-control form-control--radio form-control--advanced"
     *     },
     *     "value_options": {"Y": "application.signature.options.verify"},
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $signatureVerifyMandate;

    /**
     * @Form\Attributes({"value": "markup-signature-disabled-text"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $disabledReview;

    /**
     * @Form\Attributes({"id":"declarationDownload"})
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $declarationDownload;

    /**
     * @Form\Attributes({"value": "markup-declaration-for-verify","data-container-class":"declarationForVerify"})
     * @Form\Type("Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $declarationForVerify;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;
}
