<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("sections")
 * @Form\Options({"label":"Select sections to include"})
 */
class HtmlEditorSections
{
    /**
     * @Form\Attributes({
     *     "id": "sections-financial",
     *     "class": "govuk-checkbox"
     * })
     * @Form\Options({
     *     "label": "Financial Information",
     *     "label_attributes": {
     *         "class": "govuk-label"
     *     },
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "use_hidden_element": true
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $financial = null;

    /**
     * @Form\Attributes({
     *     "id": "sections-personnel",
     *     "class": "govuk-checkbox"
     * })
     * @Form\Options({
     *     "label": "Personnel Information",
     *     "label_attributes": {
     *         "class": "govuk-label"
     *     },
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "use_hidden_element": true
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $personnel = null;

    /**
     * @Form\Attributes({
     *     "id": "sections-operating-centers",
     *     "class": "govuk-checkbox"
     * })
     * @Form\Options({
     *     "label": "Operating Centers",
     *     "label_attributes": {
     *         "class": "govuk-label"
     *     },
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "use_hidden_element": true
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $operatingCenters = null;

    /**
     * @Form\Attributes({
     *     "id": "sections-vehicles",
     *     "class": "govuk-checkbox"
     * })
     * @Form\Options({
     *     "label": "Vehicles Information",
     *     "label_attributes": {
     *         "class": "govuk-label"
     *     },
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "use_hidden_element": true
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $vehicles = null;
}
