<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("simple-search")
 * @Form\Attributes({"method":"post", "action":"", "role":"search"})
 * @Form\Hydrator("Laminas\Stdlib\Hydrator\ArraySerializable")
 */
class SimpleSearch
{
    /**
     * @Form\Attributes({"class": "long", "placeholder": "", "label":"some"})
     * @Form\Type("Text")
     * @Form\Validator({"name": "NotEmpty"})
     * @Form\Options({
     *     "label": "search.form.label",
     *     "error-message": "simpleSearch_search-error"
     * })
     */
    protected $search;

    /**
     * @Form\Options({
     *     "label": "lookup-basic-search-submit",
     * })
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button"
     * })
     * @Form\Type("Button")
     */
    protected $submit;

    /**
     * @Form\Type("Hidden")
     */
    protected $index;
}
