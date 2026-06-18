<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Vehicle Search
 * @Form\Options({
 *     "label": "vehicle-search-vrm",
 * })
 */
class VehicleSearch
{
    /**
     * @Form\Attributes({"id":"vrm"})
     * @Form\Type("\Laminas\Form\Element\Text")
     * @Form\Required(true)
     */
    public $vrm;

    /**
     * @Form\Attributes({"type":"submit","class":"govuk-button"})
     * @Form\Options({
     *     "label": "vehicle-search-search",
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter;

    /**
     * @Form\Attributes({"type":"submit","class":"govuk-button govuk-button--secondary","id":"clearSearch"})
     * @Form\Options({
     *     "label": "vehicle-search-clear-search"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $clearSearch;
}
