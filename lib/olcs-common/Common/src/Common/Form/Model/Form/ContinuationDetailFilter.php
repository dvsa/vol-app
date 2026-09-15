<?php

namespace Common\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("continuation-detail-filter")
 * @Form\Attributes({"method":"get","class":"filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ContinuationDetailFilter
{
    /**
     * @Form\Name("filters")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\ContinuationDetailFilter")
     */
    public $filters;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "data-container-class":"js-hidden",
     * })
     * @Form\Options({
     *     "label": "filter-button"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter;
}
