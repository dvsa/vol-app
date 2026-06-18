<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * DeclarationContent
 */
class DeclarationContent
{
    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Options({"tokens": {"application.review-declarations.review.director"}})
     * @Form\Attributes({"value": "markup-continuation-declaration-review"})
     */
    public $review;

    /**
     * @Form\Attributes({"value": "SET IN FORM SERVICE"})
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
}
