<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"govuk-button-group"})
 * @Form\Name("form-actions")
 */
class ClearCacheActions
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--warning",
     *     "id": "submit"
     * })
     * @Form\Options({
     *     "label": "Clear caches"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;
}
