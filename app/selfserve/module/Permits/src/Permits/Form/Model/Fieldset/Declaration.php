<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class Declaration
{
    /**
     * @Form\Name("declaration")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--declaration",
     *   "id" : "declaration",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "permits.form.declaration.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1",
     *     "not_checked_message": "error.messages.checkbox.declaration"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $declaration = null;
}
