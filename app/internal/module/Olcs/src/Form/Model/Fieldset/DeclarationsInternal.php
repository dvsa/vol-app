<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("declarationsInternal")
 */
class DeclarationsInternal
{
    /**
     * @Form\Attributes({"value": "markup-review-text-internal"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $review = null;

    /**
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $summaryDownload = null;

    /**
     * @Form\Attributes({"value": "<h3>%s</h3>" })
     * @Form\Options({"tokens": { 0: "section.name.undertakings" } })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $heading = null;

    /**
     * @Form\Options({
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "label": "application.review-declarations.confirm-text-internal",
     *     "short-label": "application.review-declarations.confirm-text-internal",
     *     "label_attributes": {"id": "label-declarationConfirmation"}
     * })
     * @Form\Attributes({"data-container-class": "confirm"})
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $declarationConfirmation = null;
}
