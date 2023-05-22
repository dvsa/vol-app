<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"govuk-button-group"})
 * @Form\Name("form-actions")
 */
class IrhpPermitPrintActions
{
    /**
     * @Form\Name("search")
     * @Form\Attributes({"type":"submit","class":"govuk-button"})
     * @Form\Options({
     *     "label": "Search"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $search = null;
}
