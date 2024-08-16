<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("replacePermit")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ReplacePermit
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"id":"permitNumber", "readonly":"true"})
     * @Form\Options({
     *     "label": "Permit Number",
     * })
     */
    public $permitNumber = null;

    /**
     * @Form\Attributes({"id":"restrictedCountries" })
     * @Form\Options({
     *     "label": "Restricted Countries",
     *      "disable_html_escape": true
     * })
     *
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $restrictedCountries = null;

    /**
     * @Form\Attributes({"id":"country" })
     * @Form\Options({
     *     "label": "Country",
     *      "disable_html_escape": true
     * })
     *
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $country = null;

    /**
     * @Form\Attributes({"id":"replacementIrhpPermit"})
     * @Form\Options({
     *     "label": "Replacement Permit Number",
     * })
     */
    public $replacementIrhpPermit = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label": "Replace"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     *     "id":"cancel",
     * })
     * @Form\Options({"label": "Cancel"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
