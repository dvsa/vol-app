<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("tm-merge")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class TransportManagerMerge
{
    /**
     * @Form\Attributes({"id":"fromTmName", "class":"extra-long", "readonly":"true"})
     * @Form\Options({
     *     "label": "form.tm-merge.from-tm-name",
     * })
     */
    public $fromTmName = null;

    /**
     * @Form\Attributes({"id":"toTmId"})
     * @Form\Options({
     *     "label": "form.tm-merge.to-tm-id",
     *     "short-label":"form.tm-merge.to-tm-id",
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     */
    public $toTmId = null;

    /**
     * @Form\Attributes({"id":"confirm"})
     * @Form\Options({
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "label":"form.tm-merge.confirm",
     *     "short-label":"form.tm-merge.confirm",
     *  })
     * @Form\Type("OlcsCheckbox")
     * @Form\Validator({"name":"Zend\Validator\Identical","options": {
     *  "token":"Y",
     *  "message":"form.tm-merge.confirm.validation"
     * }})
     */
    public $confirm = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\ConfirmButtons")
     */
    public $formActions = null;
}
