<?php

namespace Olcs\Form\Model\Fieldset\IrhpApplication;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class Bottom extends \Olcs\Form\Model\Fieldset\Base
{
    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--declaration",
     *   "id" : "declaration",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "permits.form.declaration.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $declaration = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--checked",
     *   "id" : "checked",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "permits.form.checked.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $checked = null;
}
