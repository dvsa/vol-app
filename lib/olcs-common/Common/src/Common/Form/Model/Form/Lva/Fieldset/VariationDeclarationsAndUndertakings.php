<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("declarations")
 */
class VariationDeclarationsAndUndertakings
{
    /**
     * @Form\Attributes({"value": "markup-review-text-variation"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $review;

    /**
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $summaryDownload;

    /**
     * @Form\Options({
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "must_be_value": "Y",
     *     "label": "variation.review-declarations.confirm-text",
     *     "short-label": "variation.review-declarations.confirm-short-label",
     *     "label_attributes": {
     *         "class": "form-control form-control--checkbox form-control--advanced",
     *         "id": "label-declarationConfirmation"
     *     }
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $declarationConfirmation;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "variation.review-notransportmanager.confirm-text",
     *      "value_options":{
     *          "Y":"Yes",
     *          "N":"N/A",
     *      },
     *      "label_attributes": {
     *         "class": "form-control form-control--checkbox form-control--advanced",
     *         "id": "label-noTmConfirmation"
     *     }
     * })
     */
    public $noTmConfirmation;

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
