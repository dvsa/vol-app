<?php

namespace Common\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("search")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Search
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label":"Search"})
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $submit;

    /**
     * @Form\Name("search")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Search")
     */
    public $search;

    /**
     * @Form\Name("advanced")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Advanced")
     * @Form\Options({"label":"Advanced search"})
     */
    public $advanced;
}
