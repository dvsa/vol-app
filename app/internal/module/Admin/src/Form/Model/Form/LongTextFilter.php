<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("LongTextFilter")
 * @Form\Attributes({"method":"get", "class": "filters  form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class LongTextFilter
{
    /**
     * @Form\Options({"label": "Search"})
     * @Form\Type("Text")
     * @Form\Attributes({"id":"search", "placeholder":"UID, page name or description"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $search = null;

    /**
     * @Form\Options({
     *     "label": "Language and region",
     *     "disable_inarray_validator": false,
     *     "value_options": {
     *          "" : "All",
     *          "en_GB" : "English (GB)",
     *          "cy_GB" : "Welsh (GB)",
     *          "en_NI" : "English (Northern Ireland)",
     *          "cy_NI" : "Welsh (Northern Ireland)",
     *     },
     * })
     * @Form\Type("Select")
     */
    public $locale = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label": "documents-home.submit.filter"})
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter = null;
}
