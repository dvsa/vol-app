<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lgv-declaration")
 * @Form\Attributes({
 *    "id": "lgv-declaration"
 * })
 */
class LgvDeclaration
{
    /**
     * @Form\Name("lgv-declaration-confirmation")
     * @Form\Attributes({
     *   "id": "lgv-declaration-confirmation",
     *   "data-container-class": "lgv-declaration-confirmation"
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "application_type-of-licence_licence-type.data.lgvDeclarationConfirmation",
     *     "must_be_value": "1",
     *     "not_checked_message": "lgv-undertakings.form.declaration.error",
     *     "label_attributes": {
     *         "class": "form-control form-control--checkbox form-control--advanced"
     *     }
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $lgvDeclarationConfirmation;

    /**
     * @Form\Attributes({"value":"<p class='exclamation'>%s</p>"})
     * @Form\Options({"tokens":{"application_type-of-licence_licence-type.data.lgvDeclarationWarning"}})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $lgvDeclarationWarning;
}
