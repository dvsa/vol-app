<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("merge")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class OperatorMerge
{
    /**
     * @Form\Attributes({"id":"fromOperatorName", "class":"extra-long", "readonly":"true"})
     * @Form\Options({
     *     "label": "form.operator-merge.from-operator-name",
     * })
     */
    public $fromOperatorName = null;

    /**
     * @Form\Attributes({"id":"toOperatorId"})
     * @Form\Options({
     *     "label": "form.operator-merge.to-operator-id",
     *     "short-label":"form.operator-merge.to-operator-id",
     * })
     * @Form\Validator("Laminas\Validator\Digits")
     */
    public $toOperatorId = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"licences","placeholder":"","multiple":"multiple", "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "Licences",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\Licence",
     *     "context": "fromOperatorName",
     *     "use_groups": "false",
     * })
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $licenceIds = null;

    /**
     * @Form\Attributes({"id":"confirm"})
     * @Form\Options({
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "label":"form.operator-merge.confirm",
     *     "short-label":"form.operator-merge.confirm",
     *  })
     * @Form\Type("OlcsCheckbox")
     * @Form\Validator("Laminas\Validator\Identical", options={
     *  "token":"Y",
     *  "message":"form.operator-merge.confirm.validation"
     * })
     */
    public $confirm = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\ConfirmButtons")
     */
    public $formActions = null;
}
