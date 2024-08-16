<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("GrantAuthority")
 * @Form\Options({"label":""})
 * @Form\Attributes({"method":"post"})
 */
class GrantAuthorityForm
{
    public const FIELD_GRANT_AUTHORITY = 'grant-authority';

    /**
     * @Form\Name(Olcs\Form\Model\Form\GrantAuthorityForm::FIELD_GRANT_AUTHORITY)
     * @Form\Required(true)
     * @Form\Type("DynamicRadio")
     * @Form\Options({
     *      "label": "internal.application.decisions.grant.authority.label",
     *      "hint": "internal.application.decisions.grant.authority.hint",
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      },
     *     "category": "application_grant_authority",
     * })
     * @Form\Validator("Laminas\Validator\InArray", options={
     *     "haystack": {
     *         \Common\RefData::GRANT_AUTHORITY_DELEGATED,
     *         \Common\RefData::GRANT_AUTHORITY_TC,
     *         \Common\RefData::GRANT_AUTHORITY_TR
     *     },
     *     "messages": {
     *          Laminas\Validator\InArray::NOT_IN_ARRAY: "internal.application.decisions.grant.authority.validation.invalid"
     *     }
     * })
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *      options={
     *          "messages":{Laminas\Validator\NotEmpty::IS_EMPTY:"internal.application.decisions.grant.authority.validation.invalid"}
     *      }
     * )
     */
    public $grantAuthority;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\GrantAuthorityFormActions")
     */
    public $formActions = null;
}
