<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class BusRegBrowseButtons
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label": "selfserve.search.busreg.browse.form.submit.label"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    protected $submit;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     * })
     * @Form\Options({"label": "selfserve.search.busreg.browse.form.export.label"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    protected $export;
}
